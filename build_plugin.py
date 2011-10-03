#!/usr/bin/env python
import os
import json
import shutil
import subprocess

def replace_version(text, version):
  return text.replace('{{VERSION}}', version)

handler = open('plugin/manifest.json', 'r')
manifest = json.load(handler)
version = manifest.get('version')
handler.close()

hecto_xml_tmpl = open('hecto.xml.tmpl', 'r').read()
open('hecto.xml', 'w').write(
  replace_version(
    hecto_xml_tmpl,
    version
  )
)

main_js_content = open('plugin/main.js', 'r').read()

open('plugin/main.js', 'w').write(
  replace_version(
    main_js_content,
    version
  )
)
path = os.path.dirname(__file__)

result = subprocess.check_call([
  'google-chrome',
  '--pack-extension=%s' % os.path.join(path, 'plugin/'),
  '--pack-extension-key=%s' % os.path.join(path, 'plugin.pem')
])

plugin = os.path.join(path, 'plugin.crx')
if os.path.isfile(plugin):
  shutil.copy2(plugin, 'hecto-%s.crx' % version)
  os.rename(plugin, os.path.join(path, 'hecto.crx'))

open('plugin/main.js', 'w').write(main_js_content)