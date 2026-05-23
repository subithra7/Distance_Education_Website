<?php
session_start();
require_once "../../db.php";

if(!isset($_SESSION['admin'])){
    header("Location: ../login.php");
    exit();
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if($id <= 0){
    die("Invalid Application ID");
}

/* Fetch Data */
$stmt = $pdo->prepare("SELECT * FROM records WHERE id=?");
$stmt->execute([$id]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$data){
    die("Application not found.");
}

/* UPLOAD FOLDER PATH */
$uploadPath = $_SERVER['DOCUMENT_ROOT']
            . "/admission/admission-form/uploads/"
            . trim($data['application_no']) . "/";
            
/* ================= UPDATE ================= */
if($_SERVER['REQUEST_METHOD'] === "POST"){

    $course_type  = trim($_POST['course_type']);
    $main_subject = trim($_POST['main_subject']);
    $medium       = trim($_POST['medium']);

    /* Foundation Rule */
    if($course_type == "UG"){
        $foundation_lang = trim($_POST['foundation_lang']);
    } else {
        $foundation_lang = "English";
    }

   if(!is_dir($uploadPath)){

    if(!mkdir($uploadPath, 0777, true)){
        die("Failed to create upload folder.");
    }
}

    function uploadFile($field, $oldFile, $uploadPath){

    if(isset($_FILES[$field]) && $_FILES[$field]['error'] == 0){

        /* DELETE OLD FILE */
        if(!empty($oldFile) && file_exists($uploadPath . $oldFile)){
            unlink($uploadPath . $oldFile);
        }

        /* CLEAN FILE NAME */
        $originalName = basename($_FILES[$field]['name']);

        $cleanName = preg_replace(
            '/[^A-Za-z0-9\.\-_]/',
            '_',
            $originalName
        );

        /* UNIQUE FILE NAME */
        $fileName = time() . "_" . $cleanName;

        $destination = $uploadPath . $fileName;

        /* MOVE FILE */
        if(move_uploaded_file($_FILES[$field]['tmp_name'], $destination)){
            return $fileName;
        }
    }

    return $oldFile;
}

    $photo             = uploadFile("photo", $data['photo'], $uploadPath);

    $sslc_file         = uploadFile("sslc_file", $data['sslc_file'], $uploadPath);

    $hsc_file          = uploadFile("hsc_file", $data['hsc_file'], $uploadPath);

    $ug_file           = uploadFile("ug_file", $data['ug_file'], $uploadPath);

    $tc_file           = uploadFile("tc_file", $data['tc_file'], $uploadPath);

    $migration_file    = uploadFile("migration_file", $data['migration_file'], $uploadPath);

    $undertaking_file  = uploadFile("undertaking_file", $data['undertaking_file'], $uploadPath);

    $update = $pdo->prepare("
    UPDATE records SET

course_type=?,
main_subject=?,
foundation_lang=?,
medium=?,

photo=?,
sslc_file=?,
hsc_file=?,
ug_file=?,
tc_file=?,
migration_file=?,
undertaking_file=?

WHERE id=?
");

    $update->execute([

    $course_type,
    $main_subject,
    $foundation_lang,
    $medium,

    $photo,
    $sslc_file,
    $hsc_file,
    $ug_file,
    $tc_file,
    $migration_file,
    $undertaking_file,

    $id
]);

    header("Location: view.php?id=".$id);
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
<title>University Admission - Edit Application</title>
<link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    
<div class="sidebar">
<h2>Distance Education</h2>

<a href="../dashboard.php">Dashboard</a>
<a href="list.php">Applications</a>
<a href="history.php">Approval History</a>
<a href="export.php">Export Approved</a>
<a href="../logout.php">Logout</a>

</div>
<div class="main">

<h1>Edit Application</h1>
<p>
Application No :
<strong><?php echo $data['application_no']; ?></strong>
</p>
<form method="POST" enctype="multipart/form-data">

<div class="card">
<h3>Course Information</h3>

<div class="form-grid">

<div class="form-group">
<label>Course Type</label>
<select name="course_type">
<option value="UG" <?= $data['course_type']=="UG"?"selected":"" ?>>Under Graduate</option>
<option value="PG" <?= $data['course_type']=="PG"?"selected":"" ?>>Post Graduate</option>
<option value="DIP" <?= $data['course_type']=="DIP"?"selected":"" ?>>Diploma</option>
<option value="CERT" <?= $data['course_type']=="CERT"?"selected":"" ?>>Certificate</option>
</select>
</div>

<div class="form-group">
<label>Main Subject</label>
<input type="text" name="main_subject"
value="<?= $data['main_subject'] ?>">
</div>

<div class="form-group">
<label>Medium of Study</label>
<select name="medium">
<option <?= $data['medium']=="English"?"selected":"" ?>>English</option>
<option <?= $data['medium']=="Tamil"?"selected":"" ?>>Tamil</option>
</select>
</div>

<div class="form-group">
<label>Foundation Language</label>

<?php if($data['course_type']=="UG"): ?>
<select name="foundation_lang">
<option <?= $data['foundation_lang']=="Tamil"?"selected":"" ?>>Tamil</option>
<option <?= $data['foundation_lang']=="Telugu"?"selected":"" ?>>Telugu</option>
<option <?= $data['foundation_lang']=="Kannada"?"selected":"" ?>>Kannada</option>
<option <?= $data['foundation_lang']=="Malayalam"?"selected":"" ?>>Malayalam</option>
<option <?= $data['foundation_lang']=="Hindi"?"selected":"" ?>>Hindi</option>
<option <?= $data['foundation_lang']=="Urdu"?"selected":"" ?>>Urdu</option>
<option <?= $data['foundation_lang']=="Sanskrit"?"selected":"" ?>>Sanskrit</option>
<option <?= $data['foundation_lang']=="Arabic"?"selected":"" ?>>Arabic</option>
<option <?= $data['foundation_lang']=="French"?"selected":"" ?>>French</option>
<option <?= $data['foundation_lang']=="Communicative English"?"selected":"" ?>>Communicative English</option>
</select>
<?php else: ?>
<input type="text" value="English" readonly>
<input type="hidden" name="foundation_lang" value="English">
<?php endif; ?>

</div>

</div>
</div>

<div class="card">
<div class="section-title">Student Photo</div>
<?php
$photoURL = "/admission/admission-form/uploads/" 
          . $data['application_no'] . "/" 
          . $data['photo'];
?>

<img src="<?php echo htmlspecialchars($photoURL); ?>" 
class="photo-preview">
<input type="file" name="photo" accept="image/*">
</div>

<div class="card">
<h3>Certificates</h3>

<div class="cert-grid">

<div class="form-group">
<label>SSLC Certificate</label>
<input type="file" name="sslc_file">
</div>

<div class="form-group">
<label>HSC Certificate</label>
<input type="file" name="hsc_file">
</div>

<div class="form-group">
<label>UG Certificate</label>
<input type="file" name="ug_file">
</div>

<div class="form-group">
<label>TC Certificate</label>
<input type="file" name="tc_file">
</div>

<div class="form-group">
<label>Migration Certificate</label>
<input type="file" name="migration_file">
</div>

<div class="form-group">
<label>Undertaking Certificate</label>
<input type="file" name="undertaking_file">
</div>

</div>
</div>

<div class="card">

<div class="action-bar">

<button class="btn approve">
Update Application
</button>

<a href="view.php?id=<?= $id ?>"
class="btn reject">
Cancel
</a>

</div>

</div>

</form>
</div>
</body>
</html>