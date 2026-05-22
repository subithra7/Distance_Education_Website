<?php
$sql = "
CREATE TABLE \"lsc_users\" (
  \"id\" SERIAL PRIMARY KEY,
  \"username\" varchar(100),
  \"password\" varchar(255),
  \"lsc_code\" varchar(20),
  \"lsc_name\" varchar(255)
);

CREATE TABLE \"course_fees\" (
  \"id\" SERIAL PRIMARY KEY,
  \"course_code\" varchar(10),
  \"course_name\" varchar(255),
  \"special_fee\" NUMERIC(10,2),
  \"tuition_fee\" NUMERIC(10,2),
  \"general_fee\" NUMERIC(10,2),
  \"vc_fee\" NUMERIC(10,2)
);

CREATE TABLE \"staff\" (
  \"id\" SERIAL PRIMARY KEY,
  \"username\" varchar(100),
  \"password\" varchar(255),
  \"course_type\" varchar(100),
  \"created_at\" timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE \"staff_users\" (
  \"id\" SERIAL PRIMARY KEY,
  \"username\" varchar(100),
  \"password\" varchar(255),
  \"department\" varchar(20),
  \"created_at\" timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
);

ALTER TABLE \"records\" 
ADD COLUMN \"gender\" varchar(20) DEFAULT NULL,
ADD COLUMN \"disability_certificate\" varchar(255) DEFAULT NULL,
ADD COLUMN \"signature_file\" varchar(255) DEFAULT NULL,
ADD COLUMN \"cc_serial_no\" varchar(50) DEFAULT NULL,
ADD COLUMN \"cc_date_of_issue\" DATE DEFAULT NULL,
ADD COLUMN \"cc_bottom_serial_no\" varchar(50) DEFAULT NULL;
";
file_put_contents('schema_only.sql', $sql, FILE_APPEND);
echo "Appended successfully.";
?>
