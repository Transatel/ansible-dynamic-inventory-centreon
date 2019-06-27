# ansible-dynamic-inventory-centreon

Use Centreon configuration as an Ansible dynamic inventory.

This project is composed of an HTTP API implemented with [Laravel](https://laravel.com/) and an [inventory script](./misc_scripts/ansible/centreon.py) written in python.

The inventory script ans its config file needs to be installed on your Ansible host and calls the HTTP API.

There is no restiction on where the later can be installed.

## Implementation details

It relies on [Transatel/lib-eloquent-centreon](https://github.com/Transatel/lib-eloquent-centreon) for accessing Centreon's dataabses and calling its [internal](https://github.com/centreon/centreon/tree/master/www/include/common/webServices/rest) REST API.

### Caching

There is caching of the list of available metrics (i.e. services providing perf data).

To force the reconstruction of the cache, just send:

```
DELETE localhost:8000/ansible/inventory/cache
```

### Groups and host variables

Centreon's Host Templates are mapped as Ansible Host Groups.

Centron Host Macros are mapped as Ansible Inventory Variable.

Macro override from multiple Host Template are taken into account.

Multi-level inheritance as well.

## Configuration

### HTTP API

Copy the [.env.example](.env.example) file into a new `.env` file.

There are are the keys you might want to edit (if you changed default values).

After modifying them, one might want to do a `php artisan config:clear` to ensure older cached values are purged.

### Centreon DB schema (configuration)

| Key                    | Description                                          |
| --                     | --                                                   |
| DB\_HOST\_CENTREON     | Domain Name or IP address to connect to the database |
| DB\_PORT\_CENTREON     | Port to connect to the database                      |
| DB\_DATABASE\_CENTREON | Name of the schema                                   |
| DB\_USERNAME\_CENTREON | Username to connect to the database                  |
| DB\_PASSWORD\_CENTREON | Password to connect to the database                  |

### Inventory script

The configuration file [centreon.ini](./misc_scripts/ansible/centreon.ini) has the following keys:

| Key          | Description           |
| --           | --                    |
| centreon.url | URL to access the API |

It needs to be dropped in the /etc/ansible/ folder or your ansible host.

## Dependencies


## Usage

### Retrieve dependencies

	$ composer update

### Launch

#### Development mode

You can quick start.

	$ php -S 0.0.0.0:8000 -t public

#### Example Apache configuration

```
<VirtualHost *:8000>
  ServerName ansible-dynamic-inventory-centreon
  DocumentRoot "/opt/ansible-dynamic-inventory-centreon/public"
  <Directory "/opt/ansible-dynamic-inventory-centreon/public/">
    Options Indexes FollowSymLinks
    AllowOverride all
    Require all granted
  </Directory>
</VirtualHost>
```

### Test

#### Retrieval of the inventory via the API

```
GET localhost:8000/inventory/list
```

#### Retrieval of the inventory via inventory script

After updating the URL of the backend in the [configuration file](./misc_scripts/ansible/centreon.ini), call:

	$ centreon.py --list

You can then call the script via Ansible. Assuming your [centreon.py](./misc_scripts/ansible/centreon.py) got dropped in /etc/ansible/:

```
$ # ansible >= 2.4
$ ansible-inventory -i /etc/ansible/centreon.py --list-hosts

$ # ansible < 2.4
$ ansible '*' -i /etc/ansible/centreon.py --list-hosts
```

#### Set as default inventory (optionnal)

In your /etc/ansible/ansible.cfg or ~/ansible.cfg configuration file:

	inventory      = /etc/ansible/centreon.py
