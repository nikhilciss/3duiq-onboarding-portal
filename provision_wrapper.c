#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <unistd.h>

int main(int argc, char *argv[]) {
    if (argc != 3) {
        fprintf(stderr, "Usage: 3duiq-provision <domain> <docroot>\n");
        return 1;
    }

    setuid(0);
    setgid(0);

    char *domain = argv[1];
    char *docroot = argv[2];
    char cmd[8192];
    char vhost_path[1024];
    FILE *fp;

    /* Write Apache VHost config */
    snprintf(vhost_path, sizeof(vhost_path), "/etc/apache2/sites-available/%s.conf", domain);
    fp = fopen(vhost_path, "w");
    if (!fp) { fprintf(stderr, "Cannot write vhost\n"); return 1; }
    fprintf(fp,
        "<VirtualHost *:80>\n"
        "    ServerName %s\n"
        "    DocumentRoot %s\n"
        "    <Directory %s>\n"
        "        Options Indexes FollowSymLinks\n"
        "        AllowOverride All\n"
        "        Require all granted\n"
        "    </Directory>\n"
        "    ErrorLog ${APACHE_LOG_DIR}/%s_error.log\n"
        "    CustomLog ${APACHE_LOG_DIR}/%s_access.log combined\n"
        "</VirtualHost>\n",
        domain, docroot, docroot, domain, domain);
    fclose(fp);

    /* Enable site */
    snprintf(cmd, sizeof(cmd), "/usr/sbin/a2ensite %s.conf > /dev/null 2>&1", domain);
    system(cmd);

    /* Add to /etc/hosts if not present */
    snprintf(cmd, sizeof(cmd), "grep -q '%s' /etc/hosts", domain);
    if (system(cmd) != 0) {
        fp = fopen("/etc/hosts", "a");
        if (fp) { fprintf(fp, "127.0.0.1 %s\n", domain); fclose(fp); }
    }

    /* Reload Apache */
    system("/bin/systemctl reload apache2 > /dev/null 2>&1");

    printf("PROVISIONED: %s\n", domain);
    return 0;
}
