import os
import sys

plist_path = 'ios/Runner/Info.plist'

if not os.path.exists(plist_path):
    print(f"ERROR: {plist_path} not found!")
    sys.exit(1)

with open(plist_path, 'r') as f:
    content = f.read()

url_types = """
	<key>CFBundleURLTypes</key>
	<array>
		<dict>
			<key>CFBundleTypeRole</key>
			<string>Editor</string>
			<key>CFBundleURLName</key>
			<string>msauth.com.rango.fatec</string>
			<key>CFBundleURLSchemes</key>
			<array>
				<string>msauth.com.rango.fatec</string>
			</array>
		</dict>
	</array>
"""

if 'msauth.com.rango.fatec' not in content:
    # Insere antes da tag de fechamento do dict
    content = content.replace('</dict>\n</plist>', url_types + '</dict>\n</plist>', 1)
    
    with open(plist_path, 'w') as f:
        f.write(content)
    print("Successfully patched Info.plist!")
else:
    print("Info.plist already patched!")

sys.exit(0)
