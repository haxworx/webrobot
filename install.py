#!/usr/bin/env python3

import sys
import re
import os

# INSTALLATION
#
# Bootstrap our crawler environment.
#
# Create settings and docker image for slurping.

class Error(Exception):
    pass

class CatOnKeyboardError(Exception):
    def __init__(self, message="Exception: Cat on keyboard!!!"):
        self.message = message
        super().__init__(self.message)
    def __str__(self):
        return self.message

MAX_ERRORS = 3

keys = ('db_host', 'db_name', 'db_user', 'db_pass', 'img')

questions = [
    { 'text': 'Please enter database host: ', 'key': 'db_host', 'answer': None },
    { 'text': 'Please enter database name: ', 'key': 'db_name', 'answer': None },
    { 'text': 'Please enter database user: ', 'key': 'db_user', 'answer': None },
    { 'text': 'Please enter database pass: ', 'key': 'db_pass', 'answer': None },
    { 'text': 'Enter docker image name: ',    'key': 'img', 'answer': None},
]

error_count = 0

answers = dict();

try:
    for question in questions:
        ok = False
        while not ok:
            print(question['text'], end= '');
            answer = input();
            matches = re.search('^[A-Za-z0-9_]+$', answer)
            if not matches:
                error_count += 1
                if error_count >= MAX_ERRORS:
                    raise CatOnKeyboardError
                continue
            else:
                answers[question['key']] = question['answer'] = answer
                ok = True
except CatOnKeyboardError as e:
    print(e, file=sys.stdout);
    sys.exit(1)

if not all(key in answers for key in keys):
    print("Missing question!", file=sys.stdout)
    sys.exit(1)

for key, value in answers.items():
    print("{} -> {}" . format(key, value));

with open('docker/setup.sh.template', 'r') as f:
    text = f.read()
    text = text . format(answers['db_host'], answers['db_name'], answers['db_user'], answers['db_pass'], answers['img'])
    with open('docker/setup.sh', 'w') as of:
        of.write(text)
with open('config.template.base', 'r') as f:
        text = f.read()
        text = text . format("{0}", "{1}", "{2}", "{3}", docker_image=answers['img'])
        with open('config.template', 'w') as of:
            of.write(text)
os.system("python3 make_config.py {} {} {} {}" . format(answers['db_host'], answers['db_name'], answers['db_user'], answers['db_pass']))
sys.exit(os.system("docker build --no-cache docker/ -t {}" . format(answers['img'])))
