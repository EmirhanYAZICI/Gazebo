import os
import subprocess
import json
import sys

def run_command(command, cwd=None):
    try:
        process = subprocess.Popen(
            command,
            cwd=cwd,
            stdout=subprocess.PIPE,
            stderr=subprocess.PIPE,
            shell=True,
            text=True
        )
        stdout, stderr = process.communicate()
        return process.returncode, stdout, stderr
    except Exception as e:
        return 1, "", str(e)

def update_spiderfoot():
    base_dir = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
    spiderfoot_dir = os.path.join(base_dir, "spiderfoot")
    
    results = {
        "git_pull": {"status": "skipped", "output": "", "error": ""},
        "pip_install": {"status": "skipped", "output": "", "error": ""},
        "success": False
    }

    if not os.path.exists(spiderfoot_dir):
        results["error"] = "SpiderFoot dizini bulunamadı."
        print(json.dumps(results))
        return

    # 1. Git Pull
    if os.path.exists(os.path.join(spiderfoot_dir, ".git")):
        code, out, err = run_command("git pull", cwd=spiderfoot_dir)
        results["git_pull"] = {
            "status": "success" if code == 0 else "failed",
            "output": out,
            "error": err
        }
    else:
        results["git_pull"]["status"] = "not_a_repo"

    # 2. Pip Install
    venv_python = os.path.join(base_dir, "venv", "Scripts", "python.exe")
    if not os.path.exists(venv_python):
        venv_python = sys.executable # Fallback to current python

    requirements_path = os.path.join(spiderfoot_dir, "requirements.txt")
    if os.path.exists(requirements_path):
        code, out, err = run_command(f'"{venv_python}" -m pip install -r requirements.txt', cwd=spiderfoot_dir)
        results["pip_install"] = {
            "status": "success" if code == 0 else "failed",
            "output": out,
            "error": err
        }
    
    results["success"] = results["git_pull"]["status"] in ["success", "not_a_repo"] and results["pip_install"]["status"] in ["success", "skipped"]
    
    print(json.dumps(results, indent=2))

if __name__ == "__main__":
    update_spiderfoot()
