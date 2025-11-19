<?php

require_once "../phpFunctions/gad_portal.php";
$con = con();

//
// ✅ EMPLOYEE SEED (1–40)
//
$employees_tbl = [
    [1, "alama@gt.cict.edu.ph", "09123456780", "CICT", "Sumacab", "Active"],
    [2, "ret@sumacab.cict.edu.ph", "09123456782", "CICT", "Sumacab", "Active"],

    [3, "jaysonrivera@gmail.com", "09123456789", "CICT", "Sumacab", "Active"],
    [4, "emilsabantug@gmail.com", "09357037451", "CICT", "Sumacab", "Active"],
    [5, "ronaldinbauat@gmail.com", "09836854310", "CICT", "Sumacab", "Active"],
    [6, "leonylynbensi@gmail.com", "09021742576", "CICT", "Sumacab", "Active"],
    [7, "michaelebensi@gmail.com", "09110860952", "CICT", "Sumacab", "Active"],
    [8, "jodellbulaclac@gmail.com", "09709459484", "CICT", "Sumacab", "Active"],
    [9, "ruthluciano@gmail.com", "09510868053", "CICT", "Sumacab", "Active"],
    [10, "applegraceoliveros@gmail.com", "09025411025", "CICT", "Sumacab", "Active"],
    [11, "vanessapascual@gmail.com", "09903692619", "CICT", "Sumacab", "Active"],
    [12, "henryroque@gmail.com", "09140487105", "CICT", "Sumacab", "Active"],
    [13, "ruthannsantos@gmail.com", "09761050671", "CICT", "Sumacab", "Active"],
    [14, "rosaliesison@gmail.com", "09510809880", "CICT", "Sumacab", "Active"],
    [15, "gloriaalcantara@gmail.com", "09966683542", "CICT", "Sumacab", "Active"],
    [16, "marcelinocerin@gmail.com", "09237434939", "CICT", "Sumacab", "Active"],
    [17, "ninogherrera@gmail.com", "09662398622", "CICT", "Sumacab", "Active"],
    [18, "randymaliwat@gmail.com", "09788744550", "CICT", "Sumacab", "Active"],
    [19, "jefrainpadre@gmail.com", "09134708897", "CICT", "Sumacab", "Active"],
    [20, "racquelpula@gmail.com", "09594405228", "CICT", "Sumacab", "Active"],
    [21, "jomaselsavellano@gmail.com", "09754155560", "CICT", "Sumacab", "Active"],
    [22, "christiantambio@gmail.com", "09308214243", "CICT", "Sumacab", "Active"],
    [23, "andrewvillegas@gmail.com", "09384560704", "CICT", "Sumacab", "Active"],
    [24, "melginebauat@gmail.com", "09099767411", "CICT", "Sumacab", "Active"],
    [25, "alexandercochancho@gmail.com", "09614752850", "CICT", "Sumacab", "Active"],
    [26, "roseannecochancho@gmail.com", "09408533067", "CICT", "Sumacab", "Active"],
    [27, "marcelinocollado@gmail.com", "09503669253", "CICT", "Sumacab", "Active"],
    [28, "angelitocunanan@gmail.com", "09261682213", "CICT", "Sumacab", "Active"],
    [29, "fodibelleleona@gmail.com", "09334648996", "CICT", "Sumacab", "Active"],
    [30, "reychellenabong@gmail.com", "09369326547", "CICT", "Sumacab", "Active"],
    [31, "crisnormanolipas@gmail.com", "09260344820", "CICT", "Sumacab", "Active"],
    [32, "joanamarietolentino@gmail.com", "09467297836", "CICT", "Sumacab", "Active"],
    [33, "arnolddelacruz@gmail.com", "09738402056", "CICT", "Sumacab", "Active"],
    [34, "rachelalegado@gmail.com", "09577238076", "CICT", "Sumacab", "Active"],
    [35, "josephdelcarmen@gmail.com", "09277237098", "CICT", "Sumacab", "Active"],
    [36, "princenicolas@gmail.com", "09803617404", "CICT", "Sumacab", "Active"],
    [37, "ronaldsantos@gmail.com", "09630935542", "CICT", "Sumacab", "Active"],
    [38, "jevcorpuz@gmail.com", "09292851473", "CICT", "Sumacab", "Active"],
    [39, "ralphjulaton@gmail.com", "09212788105", "CICT", "Sumacab", "Active"],
    [40, "chrisandysuarez@gmail.com", "09640104141", "CICT", "Sumacab", "Active"]
];


$stmt_emp_tbl = $con->prepare("INSERT INTO employee_tbl 
    (id, email, contact_no, department, campus, status) VALUES (?, ?, ?, ?, ?, ?)");

foreach ($employees_tbl as $emp) {
    $check = $con->prepare("SELECT id FROM employee_tbl WHERE id = ?");
    $check->bind_param("i", $emp[0]);
    $check->execute();
    $check->store_result();

    if ($check->num_rows == 0) {
        $stmt_emp_tbl->bind_param("isssss", $emp[0], $emp[1], $emp[2], $emp[3], $emp[4], $emp[5]);
        $stmt_emp_tbl->execute();
        echo "✅ Employee {$emp[1]} inserted into employee_tbl.<br>";
    } else {
        echo "⚠️ Employee {$emp[1]} already exists in employee_tbl.<br>";
    }

    $check->close();
}
$stmt_emp_tbl->close();


//
// ✅ ACCOUNTS SEED (Fixed with employee_tbl IDs)
//
$accounts = [
    [
        "id" => 1,
        "email" => "director@gt.cict.edu.ph",
        "pass" => password_hash("director", PASSWORD_DEFAULT),
        "fname" => "Alma",
        "lname" => "Galang",
        "position" => "Director",
        "department" => "CICT",
        "campus" => "Gen. Tinio"
    ],
    [
        "id" => 2,
        "email" => "ret@gt.cict.edu.ph",
        "pass" => password_hash("ret", PASSWORD_DEFAULT),
        "fname" => "Jiezel",
        "lname" => "Alcantara",
        "position" => "RET Chair",
        "department" => "CICT",
        "campus" => "Gen. Tinio"
    ],
    
];

$stmt = $con->prepare("INSERT INTO accounts_tbl 
    (id, email, pass, fname, lname, position, department, campus) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

foreach ($accounts as $acc) {
    $check = $con->prepare("SELECT id FROM accounts_tbl WHERE email = ?");
    $check->bind_param("s", $acc['email']);
    $check->execute();
    $check->store_result();

    if ($check->num_rows == 0) {
        $stmt->bind_param(
            "isssssss",
            $acc['id'],
            $acc['email'],
            $acc['pass'],
            $acc['fname'],
            $acc['lname'],
            $acc['position'],
            $acc['department'],
            $acc['campus']
        );
        $stmt->execute();
        echo "✅ Account `{$acc['email']}` inserted.<br>";
    } else {
        echo "⚠️ Account `{$acc['email']}` already exists.<br>";
    }

    $check->close();
}
$stmt->close();

//
// ✅ EMPLOYEE INFO SEED (Full, 1–40)
//
$employees_info = [
    // IDs 1–2 (also in accounts_tbl)
    [1, "Alma", "G", "Galang", "Mabini, Cabanatuan City", "1985-01-15", "Married", "Female", "Female", "None", "L", "40000-50000", 1, 2, "No concern"],
    [2, "Jiezel", "R", "Alcantara", "Ramos Avenue, Cabanatuan City", "1988-09-10", "Single", "Female", "Female", "None", "M", "40000-50000", 2, 1, "No concern"],

    // IDs 3–40 (rest of employees)
    [3, "Jayson", "R", "Rivera", "Sumacab St., Cabanatuan City", "1999-10-10", "Single", "Male", "Male", "PWD", "L", "Above 65000", 3, 0, "No concern"],
    [4, "Emilsa", "T", "Bantug", "Sumacab St., Cabanatuan City", "1981-01-08", "Single", "Female", "Female", "PWD", "L", "Above 65000", 4, 0, "No concern"],
    [5, "Ronaldin", "V", "Bauat", "Sumacab St., Cabanatuan City", "1986-11-11", "Single", "Male", "Male", "PWD", "L", "Above 65000", 5, 0, "No concern"],
    [6, "Leonylyn", "P", "Bensi", "Sumacab St., Cabanatuan City", "1971-04-24", "Single", "Female", "Female", "PWD", "L", "Above 65000", 6, 0, "No concern"],
    [7, "Michael", "E", "Bensi", "Sumacab St., Cabanatuan City", "2000-11-14", "Single", "Male", "Male", "PWD", "L", "Above 65000", 7, 0, "No concern"],
    [8, "Jodell", "R", "Bulaclac", "Sumacab St., Cabanatuan City", "1965-09-20", "Single", "Male", "Male", "PWD", "L", "Above 65000", 8, 0, "No concern"],
    [9, "Ruth", "G", "Luciano", "Sumacab St., Cabanatuan City", "1970-07-10", "Single", "Female", "Female", "PWD", "L", "Above 65000", 9, 0, "No concern"],
    [10, "Apple Grace", "G", "Oliveros", "Sumacab St., Cabanatuan City", "1976-05-22", "Single", "Female", "Female", "PWD", "L", "Above 65000", 10, 0, "No concern"],
    [11, "Vanessa", "C", "Pascual", "Sumacab St., Cabanatuan City", "2000-10-10", "Single", "Female", "Female", "PWD", "L", "Above 65000", 11, 0, "No concern"],
    [12, "Henry", "T", "Roque", "Sumacab St., Cabanatuan City", "1975-05-25", "Single", "Male", "Male", "PWD", "L", "Above 65000", 12, 0, "No concern"],
    [13, "Ruth Ann", "G", "Santos", "Sumacab St., Cabanatuan City", "1981-03-10", "Single", "Female", "Female", "PWD", "L", "Above 65000", 13, 0, "No concern"],
    [14, "Rosalie", "B", "Sison", "Sumacab St., Cabanatuan City", "1975-11-12", "Single", "Female", "Female", "PWD", "L", "Above 65000", 14, 0, "No concern"],
    [15, "Gloria", "M", "Alcantara", "Sumacab St., Cabanatuan City", "1988-04-07", "Single", "Female", "Female", "PWD", "L", "Above 65000", 15, 0, "No concern"],
    [16, "Marcelino", "S", "Cerin III", "Sumacab St., Cabanatuan City", "1982-05-29", "Single", "Male", "Male", "PWD", "L", "Above 65000", 16, 0, "No concern"],
    [17, "Niño", "G", "Herrera", "Sumacab St., Cabanatuan City", "1961-10-17", "Single", "Male", "Male", "PWD", "L", "Above 65000", 17, 0, "No concern"],
    [18, "Randy", "R", "Maliwat", "Sumacab St., Cabanatuan City", "1973-01-13", "Single", "Male", "Male", "PWD", "L", "Above 65000", 18, 0, "No concern"],
    [19, "Jefrain", "M", "Padre", "Sumacab St., Cabanatuan City", "1986-03-15", "Single", "Male", "Male", "PWD", "L", "Above 65000", 19, 0, "No concern"],
    [20, "Racquel", "L", "Pula", "Sumacab St., Cabanatuan City", "1996-08-26", "Single", "Female", "Female", "PWD", "L", "Above 65000", 20, 0, "No concern"],
    [21, "Jomasel", "G", "Savellano", "Sumacab St., Cabanatuan City", "1984-04-23", "Single", "Female", "Female", "PWD", "L", "Above 65000", 21, 0, "No concern"],
    [22, "Christian Noli", "C", "Tambio", "Sumacab St., Cabanatuan City", "1986-04-13", "Single", "Male", "Male", "PWD", "L", "Above 65000", 22, 0, "No concern"],
    [23, "Andrew Caezar", "A", "Villegas", "Sumacab St., Cabanatuan City", "1981-07-09", "Single", "Male", "Male", "PWD", "L", "Above 65000", 23, 0, "No concern"],
    [24, "Melgine", "M", "Bauat", "Sumacab St., Cabanatuan City", "1973-07-01", "Single", "Female", "Female", "PWD", "L", "Above 65000", 24, 0, "No concern"],
    [25, "Alexander", "S", "Cochancho", "Sumacab St., Cabanatuan City", "1961-09-06", "Single", "Male", "Male", "PWD", "L", "Above 65000", 25, 0, "No concern"],
    [26, "Rose Anne", "G", "Cochancho", "Sumacab St., Cabanatuan City", "1969-10-20", "Single", "Female", "Female", "PWD", "L", "Above 65000", 26, 0, "No concern"],
    [27, "Marcelino", "C", "Collado Jr.", "Sumacab St., Cabanatuan City", "1963-01-14", "Single", "Male", "Male", "PWD", "L", "Above 65000", 27, 0, "No concern"],
    [28, "Angelito", "I", "Cunanan Jr.", "Sumacab St., Cabanatuan City", "1979-01-22", "Single", "Male", "Male", "PWD", "L", "Above 65000", 28, 0, "No concern"],
    [29, "Fodibelle", "F", "Leona", "Sumacab St., Cabanatuan City", "1981-05-29", "Single", "Female", "Female", "PWD", "L", "Above 65000", 29, 0, "No concern"],
    [30, "Reychelle", "G", "Nabong", "Sumacab St., Cabanatuan City", "1967-06-02", "Single", "Female", "Female", "PWD", "L", "Above 65000", 30, 0, "No concern"],
    [31, "Cris Norman", "P", "Olipas", "Sumacab St., Cabanatuan City", "1962-05-08", "Single", "Male", "Male", "PWD", "L", "Above 65000", 31, 0, "No concern"],
    [32, "Joana Marie", "C", "Tolentino", "Sumacab St., Cabanatuan City", "1975-06-24", "Single", "Female", "Female", "PWD", "L", "Above 65000", 32, 0, "No concern"],
    [33, "Arnold", "P", "Dela Cruz", "Sumacab St., Cabanatuan City", "1991-01-22", "Single", "Male", "Male", "PWD", "L", "Above 65000", 33, 0, "No concern"],
    [34, "Rachel", "T", "Alegado", "Sumacab St., Cabanatuan City", "1997-04-12", "Single", "Female", "Female", "PWD", "L", "Above 65000", 34, 0, "No concern"],
    [35, "Joseph", "R", "Del Carmen", "Sumacab St., Cabanatuan City", "1990-06-26", "Single", "Male", "Male", "PWD", "L", "Above 65000", 35, 0, "No concern"],
    [36, "Prince Mert", "O", "Nicolas", "Sumacab St., Cabanatuan City", "1973-11-03", "Single", "Male", "Male", "PWD", "L", "Above 65000", 36, 0, "No concern"],
    [37, "Ronald", "S", "Santos", "Sumacab St., Cabanatuan City", "1962-07-01", "Single", "Male", "Male", "PWD", "L", "Above 65000", 37, 0, "No concern"],
    [38, "Jev", "D", "Corpuz", "Sumacab St., Cabanatuan City", "1973-09-01", "Single", "Male", "Male", "PWD", "L", "Above 65000", 38, 0, "No concern"],
    [39, "Ralph Angelo", "M", "Julaton", "Sumacab St., Cabanatuan City", "1996-12-31", "Single", "Male", "Male", "PWD", "L", "Above 65000", 39, 0, "No concern"],
    [40, "Chrisandy", "B", "Suarez", "Sumacab St., Cabanatuan City", "1976-04-24", "Single", "Male", "Male", "PWD", "L", "Above 65000", 40, 0, "No concern"]
];


$stmt_emp_info = $con->prepare("
    INSERT INTO employee_info 
    (id, fname, m_initial, lname, address, birthday, marital_status, sex, gender, priority_status, size, income, employee_id, children_num, concern) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");

foreach ($employees_info as $emp) {
    $check = $con->prepare("SELECT id FROM employee_info WHERE id = ?");
    $check->bind_param("i", $emp[0]);
    $check->execute();
    $check->store_result();

    if ($check->num_rows == 0) {
        $stmt_emp_info->bind_param(
            "isssssssssssiis",
            $emp[0],
            $emp[1],
            $emp[2],
            $emp[3],
            $emp[4],
            $emp[5],
            $emp[6],
            $emp[7],
            $emp[8],
            $emp[9],
            $emp[10],
            $emp[11],
            $emp[12],
            $emp[13],
            $emp[14]
        );
        $stmt_emp_info->execute();
        echo "✅ Employee {$emp[1]} inserted into employee_info.<br>";
    } else {
        echo "⚠️ Employee {$emp[1]} already exists in employee_info.<br>";
    }

    $check->close();
}
$stmt_emp_info->close();

//
// ✅ EVENTS SEED
//
$events = [
    ["Independence Day", "Celebration of Philippine Independence.", "2025-06-12", "Holiday", null, null, null],
    ["Christmas Break", "Christmas holiday break for all campuses.", "2027-12-24", "Holiday", null, null, null],
    ["CICT General Assembly", "Annual assembly for CICT faculty and staff.", "2025-07-05", "Event", null, null, null],
    ["Wellness Program", "Campus-wide wellness and fitness activity.", "2027-07-19", "Event", null, null, null],
    ["CICT Foundation Day", "Celebration of the CICT department's foundation.", "2025-09-01", "Event", null, null, null],
    ["Research Colloquium 2027", "Presentation of completed research works.", "2027-11-10", "Research Event", "2027-10-01", "2027-10-15", "2027-11-10"],
    ["Thesis Proposal Defense", "Defense for incoming 4th year students' proposals.", "2025-08-15", "Research Event", "2025-08-01", "2025-08-10", "2025-08-15"],
    ["Capstone Final Defense", "Final presentation for graduating students.", "2027-10-20", "Research Event", "2027-09-15", "2027-10-01", "2027-10-20"],
    ["Innovation Expo", "Showcasing innovative projects from CICT students.", "2025-12-05", "Research Event", "2025-11-01", "2025-11-20", "2025-12-05"],
    ["Faculty Research Forum", "Faculty members presenting ongoing research.", "2027-07-28", "Research Event", "2027-07-01", "2027-07-15", "2027-07-28"],
];

$stmt_event = $con->prepare("INSERT INTO announcement_tbl 
    (announceTitle, announceDesc, announceDate, category, proposalDate, acceptanceDate, presentationDate) 
    VALUES (?, ?, ?, ?, ?, ?, ?)");

foreach ($events as $ev) {
    $check = $con->prepare("SELECT id FROM announcement_tbl WHERE announceTitle = ? AND announceDate = ?");
    $check->bind_param("ss", $ev[0], $ev[2]);
    $check->execute();
    $check->store_result();

    if ($check->num_rows == 0) {
        $stmt_event->bind_param("sssssss", $ev[0], $ev[1], $ev[2], $ev[3], $ev[4], $ev[5], $ev[6]);
        $stmt_event->execute();
        echo "✅ Event `{$ev[0]}` inserted.<br>";
    } else {
        echo "⚠️ Event `{$ev[0]}` already exists.<br>";
    }

    $check->close();
}

$stmt_event->close();

//
// ✅ INVENTORY SEED
//
$inventory = [
    ["GAD T-shirt (Pink)", "GAD Pink T-Shirt for Women", null, 50, "M"],
    ["GAD T-shirt (Blue)", "GAD Blue T-Shirt for Men", null, 40, "L"],
    ["Ballpen (GAD Logo)", "Customized ballpen with GAD logo", null, 200, null],
    ["Notebook (GAD Cover)", "A5 notebook with GAD-themed cover", null, 150, null],
    ["Eco Bag", "Reusable eco bag with GAD logo", null, 100, null],
    ["Umbrella (GAD)", "Foldable umbrella with GAD print", null, 75, null],
    ["Lanyard", "ID lanyard with GAD branding", null, 300, null],
    ["Tumbler", "Stainless tumbler with GAD logo", null, 80, null],
    ["Cap (GAD)", "Adjustable cap with embroidered GAD design", null, 60, null],
    ["Jacket (GAD)", "GAD branded jacket for events", null, 20, "XL"],
];

$stmt_inventory = $con->prepare("INSERT INTO inventory_tbl 
    (itemName, itemDesc, itemImage, itemQuantity, itemSize) VALUES (?, ?, ?, ?, ?)");

foreach ($inventory as $inv) {
    $check = $con->prepare("SELECT id FROM inventory_tbl WHERE itemName = ?");
    $check->bind_param("s", $inv[0]);
    $check->execute();
    $check->store_result();

    if ($check->num_rows == 0) {
        $stmt_inventory->bind_param("sssis", $inv[0], $inv[1], $inv[2], $inv[3], $inv[4]);
        $stmt_inventory->execute();
        echo "✅ Inventory item `{$inv[0]}` inserted.<br>";
    } else {
        echo "⚠️ Inventory item `{$inv[0]}` already exists.<br>";
    }

    $check->close();
}

$stmt_inventory->close();

echo "<br>✅ All seeds executed successfully!";