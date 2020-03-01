# SampleTool

## Descripción

Plataforma para administración de Samples de Adidas, control de stock y manejo de entregas y recepción de Stock.

## Tecnologías

Lenguage: PHP 7.3
Framework: Symfony 4.2
Base de datos: MySql

## Ambiente

## Instalación del proyecto

### 1 - Clonar proyecto

```
git clone https://gitlab.magnetico.com.ar/academia/academia-api.git academia_api
```

### 2 - Instalación de vendors

```
php composer.phar install
```

### 3 - Configuración de entorno

Copiar archivo .env.dist a .env y completar datos del entorno.

```
cp .env.dist .env
```
Configuración en .env para lexik/jwt-authentication-bundle

```
JWT_PRIVATE_KEY_PATH=config/jwt/private.pem
JWT_PUBLIC_KEY_PATH=config/jwt/public.pem
JWT_PASSPHRASE=<tuPassPhrase>
JWT_TOKENTTL=3600
```
#### Creación de la Base de Datos:

```
php bin/console doctrine:database:create
```

## Generate the SSH keys

```
	$ mkdir config/jwt
	$ openssl genrsa -out config/jwt/private.pem -aes256 4096
	$ openssl rsa -pubout -in config/jwt/private.pem -out config/jwt/public.pem
```

## Test

```
    $ php bin/phpunit or ./bin/phpunit
```

## Install
1 - composer install
2 - configure .env
3 - php bin/console doctrine:database:create
4 - php bin/console make:migration
5 - php bin/console doctrine:migrations:migrate
6 - php bin/console doctrine:fixtures:load --append

## Comandos útiles

1. `php bin/console make:entity` Para generar una nueva entidad
1. `php bin/console make:migration` Para crear el archivo de migración en la DB de la entidad generada
1. `php bin/console doctrine:migrations:migrate` Para ejecutar esas migraciones en la base de datos
1. `php bin/console make:controller ProductController` Para crear un Controller (no debería ser necesario porque también genera el template de ese controller, y en nuestro caso funciona como API Rest y no necesitamos HTML)
1. `symfony server:start` o `php bin/console server:run` para levantar el proyecto, o lo pueden subir en una carpeta de Apache o nginx si tienen un server de esos configurados localmente.
1. `php bin/console doctrine:fixtures:load` to load data fixtures on the DB. 
1. You can see API endpoints docs on [api_url]/api/doc, where local **api_url** use to be `http://localhost:8000`

## Parameters to filter, order and paginate by query string in the API call

### Ordering
`?ordering[{paramName}]={ASC|DESC}` replacing **{paramName}** by the attribute you want to use to order and using **ASC** or **DESC**

If you want to apply multiple orders, you can join them:
`?ordering[paramName1]=ASC&ordering[paramName2]=DESC`

### Filtering
1. Comparing by equal -> `?filters[{filterName}]={filterValue}` where **{filterName}** is the attribute name to compare and **{filterValue}** is the value we use to filter (Usually to filter by id).
1. Comparing in array -> `?filters[{filterName}][IN][{index}]={filterValue}` where **{filterName}** is the attribute name to compare, **{index}** is the way to add array in query string, so first one is `0`, second one is `1`, etc... , and **{filterValue}** is the value to compare and apply the filter.
1. Comparing with contains -> `?filters[{filterName}][LIKE]=%{filterValue}%` where **{filterName}** is the attribute name to compare and **{filterValue}** is the value we want to check if string attribute contains.
1. Filtering with OR -> `?filters[OR][{filterName}][LIKE]=%{filterValue}%`
1. Filtering using NULL -> `?filters[{filterName}][IS]=NULL`

Where:
- **{filterName}** is the object attribute name we use to compare and filter. It could be just the attribute, or in the case it is a related entity, we can use `relationName.filterName` with dot notation to compare an attribute from the related entity.
- **{filterValue}** is the value we use to compare
- **{index}** is only when we send arrays in the query string, it is the case of [IN] filter, so it is just the way query string works, so first item will be index=0, second item will be index=1, etc...

### Pagination
`?pagination[page]={page}&pagination[size]={size}` replacing {page} by page number and {size} by number of rows you want to fetch.

So a full query could be
`?ordering[paramName1]=ASC&ordering[paramName2]=DESC&filters[param3]=3&filters[param4][IS]=NULL&pagination[page]=2&pagination[size]=10`
