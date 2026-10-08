<?php

namespace App\Http\Controllers;

use App\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CompanyController extends Controller
{
    /**
     * Show the company onboarding form.
     */
    public function create()
    {
        return view('companies.create');
    }

    /**
     * Store and provision a new company with databases and virtual hosts.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'instances' => 'required|array|min:1',
            'instances.*' => 'required|string|in:crm,reporting,api',
        ]);

        $name = trim($request->name);
        $slug = Str::slug($name, '_');
        if (empty($slug)) {
            $slug = 'comp_' . time();
        }

        // Handle Logo Upload Safely
        $logoUrl = asset('images/3duiq_banner.png');
        if ($request->hasFile('logo') && $request->file('logo')->isValid()) {
            try {
                $uploadDir = public_path('uploads/logos');
                if (!file_exists($uploadDir)) {
                    @mkdir($uploadDir, 0777, true);
                    @chmod($uploadDir, 0777);
                }

                $logoName = $slug . '_' . time() . '.' . $request->logo->extension();
                $request->logo->move($uploadDir, $logoName);
                $logoUrl = asset('uploads/logos/' . $logoName);
            } catch (\Exception $e) {
                // Fallback to default logo if move fails
                $logoUrl = asset('images/3duiq_banner.png');
            }
        }

        // 1. Create Databases and import table schemas
        $dbSuffix = Str::slug($name, '');
        $databases = [
            "tracking_{$dbSuffix}" => 'tracking.sql',
            "lcr_{$dbSuffix}"      => 'lcr.sql',
            "lcapi_{$dbSuffix}"    => 'lcapi.sql',
        ];

        $dbNames = array_keys($databases);
        $schemaBaseDir = base_path('table-structure');

        foreach ($databases as $db => $schemaFile) {
            try {
                // Create database
                DB::statement("CREATE DATABASE IF NOT EXISTS `$db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

                // Import table structure using mysql CLI (handles multi-statement SQL reliably)
                $schemaPath = $schemaBaseDir . '/' . $schemaFile;
                if (file_exists($schemaPath)) {
                    $dbHost = config('database.connections.mysql.host', '127.0.0.1');
                    $dbPort = config('database.connections.mysql.port', '3306');
                    $dbUser = config('database.connections.mysql.username', 'root');
                    $dbPass = config('database.connections.mysql.password', '');

                    $escapedPass  = escapeshellarg($dbPass);
                    $escapedUser  = escapeshellarg($dbUser);
                    $escapedHost  = escapeshellarg($dbHost);
                    $escapedDb    = escapeshellarg($db);
                    $escapedFile  = escapeshellarg($schemaPath);

                    $cmd = "mysql -h {$escapedHost} -P {$dbPort} -u {$escapedUser} -p{$dbPass} {$db} < {$escapedFile} 2>/dev/null";
                    @exec($cmd);
                }
            } catch (\Exception $e) {
                // Log or handle database creation exception if any
            }
        }

        $selectedInstances = $request->input('instances', []);
        $crmEnabled = in_array('crm', $selectedInstances);
        $reportingEnabled = in_array('reporting', $selectedInstances);
        $apiEnabled = in_array('api', $selectedInstances, true);

        $crmVhost = null;
        $reportingVhost = null;
        $apiVhost = null;

        // Base directory for instance web apps
        $instancesBaseDir = '/var/www/html/3duiq-instances';
        if (!file_exists($instancesBaseDir)) {
            @mkdir($instancesBaseDir, 0777, true);
        }

        // 2. Setup CRM Instance VHost if selected: companyname.3duiq.com
        if ($crmEnabled) {
            $crmDomain = strtolower($dbSuffix) . '.3duiq.com';
            $crmDir = $instancesBaseDir . '/' . $dbSuffix . '_crm';
            if (!file_exists($crmDir)) {
                @mkdir($crmDir, 0777, true);
            }

            // Create index.php for CRM Instance
            $this->createInstanceIndex(
                $crmDir,
                $name,
                $logoUrl,
                'CRM Portal Instance',
                $crmDomain,
                $dbNames
            );

            // Create Apache VHost Config
            $this->setupApacheVHost($crmDomain, $crmDir);
            $crmVhost = "http://{$crmDomain}";
        }

        // 3. Setup Reporting Instance VHost if selected: companynamereport.3duiq.com
        if ($reportingEnabled) {
            $repDomain = strtolower($dbSuffix) . 'report.3duiq.com';
            $repDir = $instancesBaseDir . '/' . $dbSuffix . '_report';
            if (!file_exists($repDir)) {
                @mkdir($repDir, 0777, true);
            }

            // Create index.php for Reporting Instance
            $this->createInstanceIndex(
                $repDir,
                $name,
                $logoUrl,
                'Reporting & Analytics Instance',
                $repDomain,
                $dbNames
            );

            // Create Apache VHost Config
            $this->setupApacheVHost($repDomain, $repDir);
            $reportingVhost = "http://{$repDomain}";
        }

        // 4. Setup API Instance VHost if selected: companynameapi.3duiq.com
        if ($apiEnabled) {
            $apiDomain = strtolower($dbSuffix) . 'api.3duiq.com';
            $apiDir = $instancesBaseDir . '/' . $dbSuffix . '_api';
            if (!file_exists($apiDir)) {
                @mkdir($apiDir, 0777, true);
            }

            $this->createInstanceIndex(
                $apiDir,
                $name,
                $logoUrl,
                '3duiq api',
                $apiDomain,
                $dbNames
            );

            $this->setupApacheVHost($apiDomain, $apiDir);
            $apiVhost = "http://{$apiDomain}";
        }

        // Reload Apache
        @exec('sudo systemctl reload apache2 > /dev/null 2>&1');
        @exec('sudo apache2ctl graceful > /dev/null 2>&1');

        // Save Company to Database
        $company = Company::create([
            'name' => $name,
            'slug' => $slug,
            'logo_path' => $logoUrl,
            'crm_enabled' => $crmEnabled,
            'reporting_enabled' => $reportingEnabled,
            'api_enabled' => $apiEnabled,
            'crm_vhost' => $crmVhost,
            'reporting_vhost' => $reportingVhost,
            'api_vhost' => $apiVhost,
            'databases_created' => json_encode($dbNames),
        ]);

        return redirect()->route('home')->with('onboard_success', [
            'company' => $name,
            'logo' => $logoUrl,
            'crm_vhost' => $crmVhost,
            'reporting_vhost' => $reportingVhost,
            'api_vhost' => $apiVhost,
            'databases' => $dbNames,
        ]);
    }

    /**
     * Generate simple PHP app index file for an instance.
     */
    protected function createInstanceIndex($dir, $companyName, $logoUrl, $instanceType, $domain, array $databases)
    {
        $dbListHtml = '';
        foreach ($databases as $db) {
            $dbListHtml .= '<span style="background: rgba(56, 189, 248, 0.15); border: 1px solid rgba(56, 189, 248, 0.3); color: #38bdf8; padding: 6px 14px; border-radius: 20px; font-size: 0.85rem; font-weight: 600; display: inline-block; margin: 4px;">' . htmlspecialchars($db) . '</span>';
        }

        $defaultLogo = asset('images/3duiq_banner.png');

        $code = '<?php
$companyName = ' . var_export($companyName, true) . ';
$logoUrl = ' . var_export($logoUrl, true) . ';
$defaultLogo = ' . var_export($defaultLogo, true) . ';
$instanceType = ' . var_export($instanceType, true) . ';
$domain = ' . var_export($domain, true) . ';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($companyName); ?> - <?php echo htmlspecialchars($instanceType); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            margin: 0;
            padding: 0;
            background: #060913;
            color: #f1f5f9;
            font-family: \'Plus Jakarta Sans\', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .container {
            max-width: 700px;
            width: 90%;
            background: rgba(15, 23, 42, 0.85);
            border: 1px solid rgba(56, 189, 248, 0.25);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(16px);
            text-align: center;
        }
        .logo {
            max-height: 70px;
            max-width: 250px;
            object-fit: contain;
            margin-bottom: 20px;
            border-radius: 8px;
        }
        .badge {
            background: linear-gradient(135deg, #0284c7 0%, #6366f1 100%);
            color: white;
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 700;
            display: inline-block;
            margin-bottom: 16px;
        }
        h1 {
            font-size: 2rem;
            margin-bottom: 8px;
            color: #ffffff;
        }
        .domain {
            color: #38bdf8;
            font-weight: 600;
            font-size: 1.1rem;
            margin-bottom: 24px;
        }
        .db-box {
            background: rgba(30, 41, 59, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 12px;
            padding: 20px;
            margin-top: 24px;
        }
        .status {
            color: #10b981;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            margin-top: 20px;
        }
        .status-dot {
            width: 10px;
            height: 10px;
            background: #10b981;
            border-radius: 50%;
            display: inline-block;
            box-shadow: 0 0 10px #10b981;
        }
    </style>
</head>
<body>
    <div class="container">
        <img src="<?php echo htmlspecialchars($logoUrl ?: $defaultLogo); ?>" 
             onerror="this.onerror=null; this.src=\'<?php echo htmlspecialchars($defaultLogo); ?>\';" 
             class="logo" 
             alt="<?php echo htmlspecialchars($companyName); ?>">
        
        <div><span class="badge">3duiq Provisioned Instance</span></div>
        <h1><?php echo htmlspecialchars($companyName); ?></h1>
        <div class="domain">🌐 <?php echo htmlspecialchars($domain); ?> (<?php echo htmlspecialchars($instanceType); ?>)</div>

        <div class="db-box">
            <h4 style="margin-top: 0; color: #94a3b8; font-size: 0.95rem;">CONNECTED DATABASES</h4>
            <div>
                ' . $dbListHtml . '
            </div>
        </div>

        <div class="status">
            <span class="status-dot"></span> Virtual Host & Database Instance Active
        </div>
    </div>
</body>
</html>';

        @file_put_contents($dir . '/index.php', $code);
    }

    /**
     * Provision Apache VHost by writing a job file to the queue directory.
     * The 3duiq-worker.service (running as root) picks up and processes the job.
     * This bypasses all Apache PHP exec() restrictions.
     */
    protected function setupApacheVHost($domain, $documentRoot)
    {
        $queueDir = '/var/spool/3duiq-queue';
        $jobFile = $queueDir . '/' . $domain . '_' . time() . '.job';

        $jobContent = "DOMAIN=\"{$domain}\"\nDOCROOT=\"{$documentRoot}\"\n";
        @file_put_contents($jobFile, $jobContent);
        @chmod($jobFile, 0666);

        // Wait up to 5 seconds for the worker to process the job
        $waited = 0;
        while (file_exists($jobFile) && $waited < 5) {
            sleep(1);
            $waited++;
        }
    }
}
