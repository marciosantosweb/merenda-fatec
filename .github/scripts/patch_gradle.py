import os
import sys

# Find the app-level build.gradle or build.gradle.kts
for root, dirs, files in os.walk('android'):
    for fname in files:
        if fname in ('build.gradle.kts', 'build.gradle') and os.path.basename(root) == 'app':
            path = os.path.join(root, fname)
            print(f"Found: {path}")
            with open(path, 'r') as f:
                content = f.read()

            is_kts = path.endswith('.kts')
            flag = 'isCoreLibraryDesugaringEnabled = true' if is_kts else 'coreLibraryDesugaringEnabled true'
            dep = 'coreLibraryDesugaring("com.android.tools:desugar_jdk_libs:2.0.4")' if is_kts else "coreLibraryDesugaring 'com.android.tools:desugar_jdk_libs:2.0.4'"

            if flag not in content:
                content = content.replace('compileOptions {', 'compileOptions {\n        ' + flag, 1)
                print(f"Added desugaring flag: {flag}")

            if dep not in content:
                content = content.replace('dependencies {', 'dependencies {\n    ' + dep, 1)
                print(f"Added desugaring dep: {dep}")

            with open(path, 'w') as f:
                f.write(content)

            print(f"Successfully patched {path}!")
            sys.exit(0)

print("ERROR: app/build.gradle(.kts) not found!")
sys.exit(1)
