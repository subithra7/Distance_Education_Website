import re
import json

with open("c:/xampp/htdocs/admission/admission-form/ap1.php", "r", encoding="utf-8") as f:
    content = f.read()

# Replace the top PHP block
top_php = """<?php
session_start();
require "db.php";

/* ===== FETCH STATES ===== */
$states = $pdo->query("SELECT * FROM states ORDER BY state_name ASC")->fetchAll();

/* ===== FETCH DISTRICTS ===== */
$districts = $pdo->query("SELECT * FROM districts ORDER BY district_name ASC")->fetchAll();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $lsc_code = isset($_SESSION['lsc_code']) ? $_SESSION['lsc_code'] : NULL;
    /* ===== DOB VALIDATION ===== */
    if (empty($_POST['dob'])) {
        die("Date of Birth required.");
    }
    $dobDate = new DateTime($_POST['dob']);
    $today   = new DateTime();
    $age     = $today->diff($dobDate)->y;
    if ($age < 17) {
        die("Applicant must be at least 17 years old.");
    }
    
    $temp_session_id = session_id();
    
    /* ===== PHOTO UPLOAD ===== */
    $photoName = $_SESSION['step1_data']['photo'] ?? null;

    if (!empty($_FILES['photo']['name']) && $_FILES['photo']['error'] == UPLOAD_ERR_OK) {
        $allowed = ['jpg','jpeg','png'];
        $photoExt = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));

        if (!in_array($photoExt, $allowed)) {
            die("Invalid photo format.");
        }

        if ($_FILES['photo']['size'] > 2 * 1024 * 1024) {
            die("Photo size must be below 2MB.");
        }

        $uploadDir = "uploads/temp_" . $temp_session_id . "/";

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $photoName = "PHOTO_" . time() . "." . $photoExt;

        move_uploaded_file(
            $_FILES['photo']['tmp_name'],
            $uploadDir . $photoName
        );
    }

    /* ===== DIFFERENTLY ABLED CERTIFICATE ===== */
    $disability_certificate = $_SESSION['step1_data']['disability_certificate'] ?? null;

    if (!empty($_POST['special_status']) 
        && $_POST['special_status'] != "None"
        && !empty($_FILES['special_file']['name']) && $_FILES['special_file']['error'] == UPLOAD_ERR_OK) {

        $uploadDir = "uploads/temp_" . $temp_session_id . "/";

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $certExt = strtolower(pathinfo($_FILES['special_file']['name'], PATHINFO_EXTENSION));

        $specialFileName = "DIFFERENTLYABLED_" . time() . "." . $certExt;

        move_uploaded_file(
            $_FILES['special_file']['tmp_name'],
            $uploadDir . $specialFileName
        );

        $disability_certificate = $specialFileName;
    }

    /* ===== FOUNDATION LANGUAGE SAFETY ===== */
    if ($_POST['course_type'] !== "UG") {
        $_POST['foundation_lang'] = null;
    }

    $differently_abled = $_POST['special_status'] ?? "None";

    /* ===== STORE IN SESSION ===== */
    $_SESSION['step1_data'] = array_merge($_POST, [
        'photo' => $photoName,
        'disability_certificate' => $disability_certificate,
        'age' => $age,
        'differently_abled' => $differently_abled,
        'special_status' => $_POST['special_status'] ?? 'None',
        'lsc_code' => $lsc_code
    ]);

    header("Location: ap2.php");
    exit;
}

$s1 = $_SESSION['step1_data'] ?? [];
?>"""

content = re.sub(r'<\?php\s*session_start\(\);.*?header\("Location: ap2\.php"\);\s*exit;\s*\}', top_php, content, flags=re.DOTALL)

# Add pre-fill values to simple text, date, and email inputs
content = re.sub(r'<input\s+type="(text|email|date)"\s+name="([^"]+)"(.*?)>', lambda m: f'<input type="{m.group(1)}" name="{m.group(2)}" value="<?php echo htmlspecialchars($s1[\'{m.group(2)}\'] ?? \'\'); ?>" {m.group(3)}>', content)

# Inject JS at the bottom for pre-selecting selects and radios
js_injection = """
<script>
document.addEventListener("DOMContentLoaded", function () {
    const s1 = <?php echo json_encode($s1); ?>;
    if (!s1 || Object.keys(s1).length === 0) return;

    // Radios
    const setRadio = (name, value) => {
        if (!value) return;
        const radios = document.querySelectorAll(`input[type='radio'][name='${name}']`);
        radios.forEach(r => { if (r.value === value) r.checked = true; });
    };
    
    setRadio('foundation_lang', s1.foundation_lang);
    setRadio('medium', s1.medium);
    setRadio('special_status', s1.special_status);
    setRadio('gender', s1.gender);
    setRadio('community', s1.community);
    setRadio('employment_status', s1.employment_status);
    setRadio('employment_type', s1.employment_type);

    // Call toggles based on radio states
    if (typeof toggleSpecialFile === 'function') toggleSpecialFile();
    if (s1.employment_status === 'yes') {
        const empOpts = document.getElementById("employmentOptions");
        if(empOpts) empOpts.style.display = "block";
    }

    // Course type
    if (s1.course_type) {
        document.getElementById("course_type").value = s1.course_type;
        // Since loadCourses is async and sets innerHTML, we override fetch to intercept or just modify the loadCourses
        // Actually, we can just wait for fetch to complete.
        let originalFetch = window.fetch;
        window.fetch = async function () {
            let response = await originalFetch.apply(this, arguments);
            let clone = response.clone();
            if (arguments[0].includes("fetch_courses.php")) {
                setTimeout(() => {
                    document.getElementById("programme_name").value = s1.programme_name;
                    document.getElementById("programme_name").dispatchEvent(new Event('change'));
                    setTimeout(() => {
                        document.getElementById("main_subject").value = s1.main_subject;
                    }, 100);
                }, 100);
            }
            if (arguments[0].includes("fetch_states.php")) {
                setTimeout(() => {
                    document.getElementById("state").value = s1.state;
                    document.getElementById("state").dispatchEvent(new Event('change'));
                }, 100);
            }
            if (arguments[0].includes("fetch_districts.php")) {
                setTimeout(() => {
                    document.getElementById("district").value = s1.district;
                }, 100);
            }
            return response;
        };
        
        loadCourses();
        toggleFoundation();
    }
});
</script>
</body>
"""

content = content.replace("</body>", js_injection, 1)

with open("c:/xampp/htdocs/admission/admission-form/ap1.php", "w", encoding="utf-8") as f:
    f.write(content)
print("ap1.php rewritten successfully")
