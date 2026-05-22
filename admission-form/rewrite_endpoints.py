import re

def rewrite_forms():
    # Fix ap1.php
    with open("c:/xampp/htdocs/admission/admission-form/ap1.php", "r", encoding="utf-8") as f:
        content1 = f.read()
    
    # Strip the if ($_SERVER["REQUEST_METHOD"] === "POST") block
    # We substitute everything from if ($_SERVER... up to exit; \n }
    content1 = re.sub(
        r'if \(\$_SERVER\["REQUEST_METHOD"\] === "POST"\) \{.*?header\("Location: ap2\.php"\);\s*exit;\s*\}',
        '',
        content1,
        flags=re.DOTALL
    )
    # Update form action
    content1 = re.sub(
        r'<form[^>]*>',
        '<form action="process.php?action=step3" method="POST" enctype="multipart/form-data">',
        content1,
        count=1
    )
    with open("c:/xampp/htdocs/admission/admission-form/ap1.php", "w", encoding="utf-8") as f:
        f.write(content1)

    # Fix ap2.php
    with open("c:/xampp/htdocs/admission/admission-form/ap2.php", "r", encoding="utf-8") as f:
        content2 = f.read()

    # Strip the if ($_SERVER["REQUEST_METHOD"] === "POST") block in ap2.php
    content2 = re.sub(
        r'if \(\$_SERVER\["REQUEST_METHOD"\] === "POST"\) \{.*?header\("Location: ap3\.php"\);\s*exit;\s*\}',
        '',
        content2,
        flags=re.DOTALL
    )
    # Note: ap2.php uses <form action="ap2.php"... we want action="process.php?action=step6"
    content2 = re.sub(
        r'<form[^>]*>',
        '<form action="process.php?action=step6" method="POST" enctype="multipart/form-data" autocomplete="off">',
        content2,
        count=1
    )
    with open("c:/xampp/htdocs/admission/admission-form/ap2.php", "w", encoding="utf-8") as f:
        f.write(content2)

if __name__ == "__main__":
    rewrite_forms()
    print("Done")
