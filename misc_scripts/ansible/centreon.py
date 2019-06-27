#!/usr/bin/env python

# need to: pipenv install requests

import requests
import configparser
import argparse


# -------------------------------------------------------------------------
# parse arguments

parser = argparse.ArgumentParser()
parser.add_argument('--list', action='store_true', help='List active servers')
parser.add_argument('--host', help='List details about the specific host')
args = parser.parse_args()


# -------------------------------------------------------------------------
# parse config

CONFIG_FILE = '/etc/ansible/centreon.ini'

config = configparser.SafeConfigParser()
config.read(CONFIG_FILE)

url = 'localhost/ansible/inventory/'
if config.has_option('centreon', 'url'):
    url = config.get('centreon', 'url')


# -------------------------------------------------------------------------
# call Centreon inventory API

if args.list:
    url += "list"
elif args.host:
    url += "host/" + args.host

resp = requests.get(url=url)


# -------------------------------------------------------------------------
# answer

# resp.encoding = 'ASCII'
print(resp.text)
