<?php

require_once "../phpFunctions/gad_portal.php";
$con = con();

//
// ✅ ACCOUNTS SEED
//
$accounts = [
    [
        "email" => "admin@example.com",
        "username" => "admin",
        "pass" => password_hash("admin", PASSWORD_DEFAULT),
        "fname" => "System",
        "lname" => "Admin",
        "position" => "Director",
        "department" => "CICT",
        "campus" => "Sumacab"
    ],
    [
        "email" => "jane.doe@example.com",
        "username" => "focal",
        "pass" => password_hash("focal", PASSWORD_DEFAULT),
        "fname" => "Jane",
        "lname" => "Doe",
        "position" => "Focal Person",
        "department" => "CICT",
        "campus" => "Sumacab"
    ],
    [
        "email" => "john.smith@example.com",
        "username" => "ta",
        "pass" => password_hash("ta", PASSWORD_DEFAULT),
        "fname" => "John",
        "lname" => "Smith",
        "position" => "Technical Assistant",
        "department" => "CICT",
        "campus" => "Sumacab"
    ],
    [
        "email" => "jayson.rivera@example.com",
        "username" => "ret",
        "pass" => password_hash("ret", PASSWORD_DEFAULT),
        "fname" => "Jayson",
        "lname" => "Rivera",
        "position" => "RET Chair",
        "department" => "CICT",
        "campus" => "Sumacab"
    ],
    [
        "email" => "ivan.kyle@example.com",
        "username" => "res",
        "pass" => password_hash("res", PASSWORD_DEFAULT),
        "fname" => "Ivan Kyle",
        "lname" => "Samaniego",
        "position" => "Researcher",
        "department" => "CICT",
        "campus" => "Sumacab"
    ],
];

$stmt = $con->prepare("INSERT INTO accounts_tbl 
    (email, username, pass, fname, lname, position, department, campus) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

foreach ($accounts as $acc) {
    $check = $con->prepare("SELECT id FROM accounts_tbl WHERE email = ? OR username = ?");
    $check->bind_param("ss", $acc['email'], $acc['username']);
    $check->execute();
    $check->store_result();

    if ($check->num_rows == 0) {
        $stmt->bind_param(
            "ssssssss",
            $acc['email'],
            $acc['username'],
            $acc['pass'],
            $acc['fname'],
            $acc['lname'],
            $acc['position'],
            $acc['department'],
            $acc['campus']
        );
        $stmt->execute();
        echo "✅ Account `{$acc['username']}` inserted.<br>";
    } else {
        echo "⚠️ Account `{$acc['username']}` already exists.<br>";
    }

    $check->close();
}
$stmt->close();


//
// ✅ EMPLOYEE SEED (1–15)
//

// employee_tbl
$employees_tbl = [
    [1, "jaysonrivera@gmail.com", "09123456789", "CICT", "Sumacab", "Active"],
    [2, "ashleybathan@gmail.com", "09876454321", "CICT", "Sumacab", "Active"],
    [3, "henreichcatig@gmail.com", "0987123456", "CICT", "Sumacab", "Active"],
    [4, "ivankylesmaniego@gmail.com", "09123876548", "CICT", "Sumacab", "Active"],
    [5, "almagalang@gmail.com", "0998653231", "COE", "Fort Magsaysay", "Active"],
    [6, "kobegarcia@gmail.com", "09123456781", "NTP", "Fort Magsaysay", "Active"],
    [7, "mariaclara@gmail.com", "09112223344", "CICT", "Sumacab", "Active"],
    [8, "juancruz@gmail.com", "09998887766", "CICT", "Sumacab", "Active"],
    [9, "pedrosantos@gmail.com", "09115556677", "CICT", "Sumacab", "Active"],
    [10, "ana.delacruz@gmail.com", "09223334455", "CICT", "Sumacab", "Active"],
    [11, "roberto.diaz@gmail.com", "09331112222", "CICT", "Sumacab", "Active"],
    [12, "katrina.luz@gmail.com", "09117778899", "CICT", "Sumacab", "Active"],
    [13, "mark.estrada@gmail.com", "09887776655", "CICT", "Sumacab", "Active"],
    [14, "sofia.mendoza@gmail.com", "09112227788", "CICT", "Sumacab", "Active"],
    [15, "angelo.santos@gmail.com", "09995554433", "CICT", "Sumacab", "Active"],
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


// employee_info
$employees_info = [
    [1, "Jayson", "R", "Rivera", "Mabini, Cabanatuan City", "1999-10-10", "Single", "Male", "LGBTQIA+", "PWD", "L"],
    [2, "Ashley", "A", "Bathan", "Bathan Street", "1998-04-01", "Single", "Female", "Female", "None", "M"],
    [3, "Henreich", "L", "Catig", "Kapitan Pepe", "1995-04-09", "Widowed", "Male", "Male", "Senior Citizen", "M"],
    [4, "Ivan Kyle", "S", "Samaniego", "Zulueta", "1994-04-25", "Married", "Male", "Male", "Senior Citizen", "M"],
    [5, "Alma", "G", "Galang", "Gen. Tinio", "1993-04-28", "Married", "Female", "Female", "None", "4XL"],
    [6, "Kobe", "A", "Garcia", "Santa Rosa, Nueva Ecija", "2000-12-12", "Single", "Male", "LGBTQIA+", "PWD", "2XL"],
    [7, "Maria", "D", "Clara", "Bayanihan, Cabanatuan City", "1998-03-15", "Single", "Female", "Female", "None", "S"],
    [8, "Juan", "P", "Cruz", "Barangay Sumacab", "1997-07-20", "Married", "Male", "Male", "None", "L"],
    [9, "Pedro", "M", "Santos", "Quezon District", "1995-11-05", "Single", "Male", "Male", "PWD", "XL"],
    [10, "Ana", "D", "Cruz", "Nueva Ecija", "1996-08-14", "Single", "Female", "Female", "None", "M"],
    [11, "Roberto", "C", "Diaz", "Palayan City", "1992-02-19", "Married", "Male", "Male", "Senior Citizen", "L"],
    [12, "Katrina", "L", "Luz", "San Isidro", "1999-06-23", "Single", "Female", "Female", "None", "S"],
    [13, "Mark", "E", "Estrada", "Sta. Rosa", "1998-09-30", "Single", "Male", "Male", "PWD", "M"],
    [14, "Sofia", "M", "Mendoza", "Cabanatuan City", "2001-01-15", "Single", "Female", "Female", "None", "S"],
    [15, "Angelo", "S", "Santos", "Talavera", "1997-12-02", "Single", "Male", "Male", "None", "XL"],
];

$stmt_emp_info = $con->prepare("INSERT INTO employee_info 
    (id, fname, m_initial, lname, address, birthday, marital_status, sex, gender, priority_status, size) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

foreach ($employees_info as $emp) {
    $check = $con->prepare("SELECT id FROM employee_info WHERE id = ?");
    $check->bind_param("i", $emp[0]);
    $check->execute();
    $check->store_result();

    if ($check->num_rows == 0) {
        $stmt_emp_info->bind_param("issssssssss", $emp[0], $emp[1], $emp[2], $emp[3], $emp[4], $emp[5], $emp[6], $emp[7], $emp[8], $emp[9], $emp[10]);
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
    // Holidays
    ["Independence Day", "Celebration of Philippine Independence.", "2025-06-12", "Holiday", null, null, null],
    ["Christmas Break", "Christmas holiday break for all campuses.", "2027-12-24", "Holiday", null, null, null],

    // Regular Events
    ["CICT General Assembly", "Annual assembly for CICT faculty and staff.", "2025-07-05", "Event", null, null, null],
    ["Wellness Program", "Campus-wide wellness and fitness activity.", "2027-07-19", "Event", null, null, null],
    ["CICT Foundation Day", "Celebration of the CICT department’s foundation.", "2025-09-01", "Event", null, null, null],

    // Research Events
    ["Research Colloquium 2027", "Presentation of completed research works.", "2027-11-10", "Research Event", "2027-10-01", "2027-10-15", "2027-11-10"],
    ["Thesis Proposal Defense", "Defense for incoming 4th year students’ proposals.", "2025-08-15", "Research Event", "2025-08-01", "2025-08-10", "2025-08-15"],
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
    ["GAD T-shirt (Pink)", "GAD Pink T-Shirt for Women", null, 50, "M", "Women"],
    ["GAD T-shirt (Blue)", "GAD Blue T-Shirt for Men", null, 40, "L", "Men"],
    ["Ballpen (GAD Logo)", "Customized ballpen with GAD logo", null, 200, null, "Everyone"],
    ["Notebook (GAD Cover)", "A5 notebook with GAD-themed cover", null, 150, null, "Education"],
    ["Eco Bag", "Reusable eco bag with GAD logo", null, 100, null, "Everyone"],
    ["Umbrella (GAD)", "Foldable umbrella with GAD print", null, 75, null, "Everyone"],
    ["Lanyard", "ID lanyard with GAD branding", null, 300, null, "Everyone"],
    ["Tumbler", "Stainless tumbler with GAD logo", null, 80, null, "Everyone"],
    ["Cap (GAD)", "Adjustable cap with embroidered GAD design", null, 60, null, "Everyone"],
    ["Jacket (GAD)", "GAD branded jacket for events", null, 20, "XL", "LGBTQIA+"],
];

$stmt_inventory = $con->prepare("INSERT INTO inventory_tbl 
    (itemName, itemDesc, itemImage, itemQuantity, itemSize, itemCategory) 
    VALUES (?, ?, ?, ?, ?, ?)");

foreach ($inventory as $item) {
    $check = $con->prepare("SELECT id FROM inventory_tbl WHERE itemName = ?");
    $check->bind_param("s", $item[0]);
    $check->execute();
    $check->store_result();

    if ($check->num_rows == 0) {
        $stmt_inventory->bind_param("sssiss", $item[0], $item[1], $item[2], $item[3], $item[4], $item[5]);
        $stmt_inventory->execute();
        echo "✅ Inventory item `{$item[0]}` inserted.<br>";
    } else {
        echo "⚠️ Inventory item `{$item[0]}` already exists.<br>";
    }

    $check->close();
}
$stmt_inventory->close();


$con->close();
