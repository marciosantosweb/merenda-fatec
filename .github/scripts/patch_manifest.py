import os
import sys

manifest_path = 'android/app/src/main/AndroidManifest.xml'

if not os.path.exists(manifest_path):
    print(f"ERROR: {manifest_path} not found!")
    sys.exit(1)

with open(manifest_path, 'r') as f:
    content = f.read()

intent_filter = """
            <intent-filter android:label="app_links_auth">
                <action android:name="android.intent.action.VIEW" />
                <category android:name="android.intent.category.DEFAULT" />
                <category android:name="android.intent.category.BROWSABLE" />
                <data android:scheme="msauth" android:host="com.example.rango" />
            </intent-filter>
        </activity>"""

if 'android:label="app_links_auth"' not in content:
    # Insere antes do fechamento da MainActivity (buscando pelo seu fechamento padrão)
    content = content.replace('</activity>', intent_filter, 1)
    
    # Substitui o nome padrão minúsculo pelo correto com maiúscula e exclamação
    content = content.replace('android:label="rango"', 'android:label="Rango!"')
    
    with open(manifest_path, 'w') as f:
        f.write(content)
    print("Successfully patched AndroidManifest.xml with CallbackActivity!")
else:
    print("Manifest already patched!")

sys.exit(0)
