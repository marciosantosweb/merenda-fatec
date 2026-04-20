import os
import sys

manifest_path = 'android/app/src/main/AndroidManifest.xml'

if not os.path.exists(manifest_path):
    print(f"ERROR: {manifest_path} not found!")
    sys.exit(1)

with open(manifest_path, 'r') as f:
    content = f.read()

intent_filter = """
            <!-- Adicionado pelo GitHub Actions para login da Microsoft -->
            <intent-filter>
                <action android:name="android.intent.action.VIEW" />
                <category android:name="android.intent.category.DEFAULT" />
                <category android:name="android.intent.category.BROWSABLE" />
                <data android:scheme="msauth.com.rango.fatec" />
            </intent-filter>"""

if 'msauth.com.rango.fatec' not in content:
    # Insere antes da tag de fechamento da activity principal
    content = content.replace('</activity>', intent_filter + '\n        </activity>', 1)
    
    with open(manifest_path, 'w') as f:
        f.write(content)
    print("Successfully patched AndroidManifest.xml!")
else:
    print("Manifest already patched!")

sys.exit(0)
