CULabsDeployment
================

Resumen
-------
Este proyecto es con el objetivo de automatizar el despleigue de aplicaciones. Fue creado para symfony2, pero puede ser utilizado para cualquier otro framework.
El proyecto surigió para dar respuesta al siguiente problemática:
Se cuenta con una aplicación symfony, donde a través de variables de entornos se le inyectan la configuración de la base de datos. Luego con una misma instancia de la aplicación, utilizando distintos dominios (cada uno con una configuración de base datos diferentes) la aplicación se comporta como aplicaciones independientes. Con esta filosofía se puede crera un ambiente trabajo para varios clientes.

Instalación
-----------
```
php composer.phar global require "culabs/deployment:dev-master" --prefer-dist
```
Incluir el directorio ``~/.composer/vendor/bin/`` en la variable $PATH para poder ejecutar el comando.

Uso
---
Detro de la aplicación crear una carpeta con el nombre ``deployment`` y dentro crear una estructura de ficheros como esta:
```
deployment
  - data
    - schema.sql
  - config.yml
  - config_base.yml
  - site_test.yml
```
Aclarar que esto es un ejemplo, eres libre de hacer la configuración que desees.

Dentro de ``config_base.yml`` se pondría algo como esto:
```yaml
parameters:
    DocumentRoot: %APP_DIR%/web
    database_user: root
    database_password: root
    vars_environment:
        SYMFONY__DATABASE__NAME: %database_name%
        SYMFONY__DATABASE__USER: %database_user%
        SYMFONY__DATABASE__PASSWORD: %database_password%
    supervisor_options:
        autorestart: 'true'
        user: www-data
        redirect_stderr: 'false'
        stdout_logfile: %APP_DIR%/app/logs/supervisor_%database_name%.log
        stdout_logfile_maxbytes: 10MB
deployment:
    up:
        database_create:
            service: command
            command: php app/console doctrine:database:create -n
            env: %vars_environment%
        schema_create:
            service: command
            command: mysql --user=%database_user% --password=%database_password% %database_name% < %APP_DIR%/deployment/data/schema.sql
        database_migrations:
            service: command
            command: php app/console doctrine:migrations:migrate -n
            env: %vars_environment%
        cache_clear:
            service: command
            command: php app/console cache:clear -e=prod
            env: %vars_environment%
        supervisor_command1:
            service: supervisor
            key: %database_name%_command1
            filename: %APP_DIR%/app/supervisor/%database_name%.conf
            command: 'php %APP_DIR%/app/console app:command1 -e=prod'
            options: %supervisor_options%
            vars_environment: %vars_environment%
        supervisor_command2:
            service: supervisor
            append: true
            key: %database_name%_command2
            filename: %APP_DIR%/app/supervisor/%database_name%.conf
            command: 'php %APP_DIR%/app/console app:command2 -e=prod'
            options: %supervisor_options%
            vars_environment: %vars_environment%
        supervisor_restart:
            service: command
            command: service supervisor restart
        vhost:
            SetEnv: %vars_environment%
            DocumentRoot: %DocumentRoot%
            ServerName: %ServerName%
    update:
        database_update:
            service: command
            command: php app/console doctrine:migrations:migrate -n
            env: %vars_environment%
        cache_clear:
            service: command
            command: php app/console cache:clear -e=prod
            env: %vars_environment%
    down:
        vhost:
            ServerName: %ServerName%
        database_drop:
            service: command
            command: php app/console doctrine:database:drop --force -n
            env: %vars_environment%
        supervisor:
            filename: %APP_DIR%/app/supervisor/%database_name%.conf
        supervisor_restart:
            service: command
            command: service supervisor restart
```
Luego ``site_test.yml`` tendría este contenido
```yaml
imports:
    - { resource: config_base.yml }

parameters:
    ServerName: dev.sittest.com
    database_name: sittest.com
    supervisor_options:
        stdout_logfile: %APP_DIR%/app/logs/supervisor_%database_name%.log
```
Ya con esta configuración se puede instalar una nueva aplicación ejecutando las operaciones de la opción ``up`` a través del comando:
```
culabs-deployment deployment up --config-file=site_test.yml
```
Para ejecutar actualizaciones se ejecutan las operaciones de la opción ``update``
```
culabs-deployment deployment update --config-file=site_test.yml
```
Finalmente se puede desintalar una aplicación con el comando:
```
culabs-deployment deployment down --config-file=site_test.yml
```

En el caso que se tenga varias aplicaciones configuradas y se desee hacer un update a todas ellas, se debe crear el fichero ``config.yml`` con lo siguiente:
```yaml
deployment:
    batch:
        - site_test.yml
        - site_test_1.yml
        - site_test_2.yml
```
Luego se ejecuta el comando:
```
culabs-deployment deployment batch
```



















