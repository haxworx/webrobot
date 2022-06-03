#!/bin/sh
for i in $(find /var/lib/mysql); do semanage fcontext -a -t mysqld_var_run_t $i; restorecon -Rv $i; done
    
