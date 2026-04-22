import os
import sys

manifest_path = 'android/app/src/main/AndroidManifest.xml'

if not os.path.exists(manifest_path):
    print(f"ERROR: {manifest_path} not found!")
    sys.exit(1)

with open(manifest_path, 'r') as f:
    content = f.read()

callback_activity = """
        <!-- Adicionado pelo GitHub Actions para login da Microsoft via flutter_web_auth_2 -->
        <activity
            android:name="com.linusu.flutter_web_auth_2.CallbackActivity"
            android:exported="true"
            android:launchMode="singleTask">
            <intent-filter android:label="flutter_web_auth_2">
                <action android:name="android.intent.action.VIEW" />
                <category android:name="android.intent.category.DEFAULT" />
                <category android:name="android.intent.category.BROWSABLE" />
                <data android:scheme="msauth" android:host="com.example.rango" />
            </intent-filter>
        </activity>
"""

if 'com.linusu.flutter_web_auth_2.CallbackActivity' not in content:
    # Insere antes da tag de fechamento da application
    content = content.replace('</application>', callback_activity + '    </application>', 1)
    
    # Substitui o nome padrão minúsculo pelo correto com maiúscula e exclamação
    content = content.replace('android:label="rango"', 'android:label="Rango!"')
    
    with open(manifest_path, 'w') as f:
        f.write(content)
    print("Successfully patched AndroidManifest.xml with CallbackActivity!")
else:
    print("Manifest already patched!")

sys.exit(0)
