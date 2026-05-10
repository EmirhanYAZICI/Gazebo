with open('src/api_server.py', 'r', encoding='utf-8') as f:
    lines = f.readlines()[:112]

with open('src/api_server_new_bottom.py', 'r', encoding='utf-8') as f:
    new_lines = f.readlines()

with open('src/api_server.py', 'w', encoding='utf-8') as f:
    f.writelines(lines + ['\n'] + new_lines)
