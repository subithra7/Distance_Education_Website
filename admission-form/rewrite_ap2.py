import re
import json

with open("c:/xampp/htdocs/admission/admission-form/ap2.php", "r", encoding="utf-8") as f:
    content = f.read()

# Top PHP replacement
top_php = """<?php
session_start();
require_once "db.php";

if (!isset($_SESSION['step1_data'])) {
    header("Location: ap1.php");
    exit;
}
$temp_session_id = session_id();

$course_type = $_SESSION['step1_data']['course_type'] ?? '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // If they clicked Back (assuming we add a back button submit)
    if (isset($_POST['back'])) {
        header("Location: ap1.php");
        exit;
    }

    $other_course = $_POST['other_course'] ?? null;
    $other_course_details = $_POST['other_course_details'] ?? null;
    
    $defence_personnel = 0;
    $ex_servicemen = 0;

    if(isset($_POST['defence_status'])){
        if($_POST['defence_status'] == "defence"){
            $defence_personnel = 1;
        }
        if($_POST['defence_status'] == "ex"){
            $ex_servicemen = 1;
        }
    }

    $abc_status = $_POST['abc'] ?? "No";
    $abc_id = $_POST['abc_id'] ?? null;

    $abc_id_clean = preg_replace('/\s+/', '', $abc_id);

    if ($abc_status === "Yes") {
        if (!preg_match('/^[0-9]{12}$/', $abc_id_clean)) {
            die("ABC ID must be exactly 12 digits.");
        }
        $check = $pdo->prepare("SELECT COUNT(*) FROM records WHERE abc_id = :abc");
        $check->execute([':abc' => $abc_id_clean]);
        if ($check->fetchColumn() > 0) {
            die("ABC ID already exists.");
        }
    }
 
    /* =========================================
       DOCUMENT MANDATORY VALIDATION
    ========================================= */
    $mandatoryDocs = ['sslc','hsc','tc','migration','undertaking'];
    foreach ($mandatoryDocs as $doc) {
        // Only require if not already in session (from previous back/forth)
        if (empty($_FILES[$doc]['name']) && empty($_SESSION['step2_data'][$doc."_file"])) {
            die(strtoupper($doc) . " certificate is mandatory.");
        }
    }

    if ($course_type === "PG") {
        if (empty($_FILES['ug']['name']) && empty($_SESSION['step2_data']["ug_file"])) {
            die("UG certificate is mandatory for PG courses.");
        }
    }
    
    // REQUIRE SIGNATURE
    if (empty($_FILES['signature']['name']) && empty($_SESSION['step2_data']["signature_file"])) {
        die("SIGNATURE is mandatory.");
    }

    $uploadDir = "uploads/temp_" . $temp_session_id . "/";
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    $docFields = ['sslc','hsc','ug','tc','migration','undertaking','signature'];
    $allowedExt = ['pdf','jpg','jpeg','png'];
    $files = [];

    foreach ($docFields as $field) {
        // Keep existing from session if no new file is uploaded
        $files[$field] = $_SESSION['step2_data'][$field."_file"] ?? null;

        if (isset($_FILES[$field]) && $_FILES[$field]['error'] == 0) {
            $ext = strtolower(pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION));

            if (!in_array($ext, $allowedExt)) {
                die("Invalid file type for " . strtoupper($field));
            }

            if ($_FILES[$field]['size'] > 2 * 1024 * 1024) {
                die(strtoupper($field) . " exceeds 2MB");
            }

            $newFile = strtoupper($field) . "_" . time() . "_" . uniqid() . "." . $ext;

            if (move_uploaded_file($_FILES[$field]['tmp_name'], $uploadDir . $newFile)) {
                $files[$field] = $newFile;
            }
        }
    }

    $enclosures = isset($_POST['enclosures']) ? implode(",", $_POST['enclosures']) : null;

    $_SESSION['step2_data'] = array_merge($_POST, [
        'defence_personnel' => $defence_personnel,
        'ex_servicemen' => $ex_servicemen,
        'abc_id_clean' => $abc_id_clean,
        'sslc_file' => $files['sslc'],
        'hsc_file' => $files['hsc'],
        'ug_file' => $files['ug'],
        'tc_file' => $files['tc'],
        'migration_file' => $files['migration'],
        'undertaking_file' => $files['undertaking'],
        'signature_file' => $files['signature'],
        'enclosures' => $enclosures
    ]);

    header("Location: ap3.php");
    exit;
}

$s2 = $_SESSION['step2_data'] ?? [];
?>"""

content = re.sub(r'<\?php\s*session_start\(\);.*?header\("Location: print_application\.php"\);\s*exit;\s*\}', lambda m: top_php, content, flags=re.DOTALL)

# Add pre-fill values to text inputs
content = re.sub(r'<input\s+type="(text)"\s+name="([^"]+)"(.*?)>', lambda m: f'<input type="{m.group(1)}" name="{m.group(2)}" value="<?php echo htmlspecialchars($s2[\'{m.group(2)}\'] ?? \'\'); ?>" {m.group(3)}>', content)

# Remove the 'required' from file inputs if previously uploaded
file_patterns = ['sslc', 'hsc', 'ug', 'tc', 'migration', 'undertaking', 'signature']
for fp in file_patterns:
    # We replace 'required' with a php check
    if fp == 'signature':
        content = re.sub(r'<input type="file"\s+name="signature"\s+id="file_signature"\s+accept="\.jpg,\.jpeg,\.png"\s+required>', f'<input type="file" name="signature" id="file_signature" accept=".jpg,.jpeg,.png" <?php echo empty($s2["signature_file"]) ? "required" : ""; ?>>\n<?php if(!empty($s2["signature_file"])) echo "<i>(Previously uploaded: " . htmlspecialchars($s2["signature_file"]) . ")</i>"; ?>', content)
    else:
        # Regex to find the <input type="file" name="fp"...> and add required condition and previously uploaded label
        content = re.sub(rf'<input type="file"(\s+)?name="{fp}"([^>]*?)>', lambda m: f'<input type="file" name="{fp}"{m.group(2)} <?php echo empty($s2["{fp}_file"]) ? "" : ""; ?>>\n<?php if(!empty($s2["{fp}_file"])) echo "<br><i>(Previously uploaded: " . htmlspecialchars($s2["{fp}_file"]) . ")</i>"; ?>', content)

# Change the form target & back buttons
content = content.replace("<button type=\"button\" onclick=\"window.location.href='ap1.php'\">Back</button>", "<button type=\"button\" onclick=\"window.location.href='ap1.php'\">Back</button>")
# The "Back" button at the end currently is JS based, but for the final submit
content = content.replace('<button type="button" class="prevBtn">Back</button>', '<button type="button" class="prevBtn">Back</button>')

# For the form attributes: currently it's enctype="multipart/form-data" autocomplete="off"
# Let's add action="" explicitly
content = content.replace('<form method="POST" enctype="multipart/form-data" autocomplete="off">', '<form action="ap2.php" method="POST" enctype="multipart/form-data" autocomplete="off">')

# Inject JS at the bottom for pre-selecting radios/checkboxes
js_injection = """
<script>
document.addEventListener("DOMContentLoaded", function () {
    const s2 = <?php echo json_encode($s2); ?>;
    if (!s2 || Object.keys(s2).length === 0) return;

    const setRadio = (name, value) => {
        if (!value) return;
        const radios = document.querySelectorAll(`input[type='radio'][name='${name}']`);
        radios.forEach(r => { if (r.value === value) r.checked = true; });
    };

    setRadio('other_course', s2.other_course);
    setRadio('defence_status', s2.defence_status);
    setRadio('abc', s2.abc);

    if (s2.other_course === 'Yes') {
        const otherCourseBox = document.getElementById("otherCourseBox");
        if(otherCourseBox) otherCourseBox.style.display = "block";
    }

    if (s2.abc === 'Yes') {
        const abcBox = document.getElementById("abcBox");
        if(abcBox) abcBox.style.display = "block";
    }

    // Checkboxes for enclosures
    if (s2.enclosures && typeof s2.enclosures === 'string') {
        const encs = s2.enclosures.split(',');
        document.querySelectorAll('input[type="checkbox"][name="enclosures[]"]').forEach(cb => {
            if (encs.includes(cb.value)) cb.checked = true;
        });
    } else if (s2.enclosures && Array.isArray(s2.enclosures)) {
        document.querySelectorAll('input[type="checkbox"][name="enclosures[]"]').forEach(cb => {
            if (s2.enclosures.includes(cb.value)) cb.checked = true;
        });
    }
});
</script>
</body>
"""

content = content.replace("</body>", js_injection, 1)

with open("c:/xampp/htdocs/admission/admission-form/ap2.php", "w", encoding="utf-8") as f:
    f.write(content)
print("ap2.php rewritten successfully")
