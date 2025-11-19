<?php

require_once "../phpFunctions/gad_portal.php";
$con = con();

//
// ✅ EMPLOYEE SEED (1–40)
//
$employees_tbl = [
    // Accounts users first (IDs 1–6)
    [1, "admin@sumacab.cict.edu.ph", "09123456780", "CICT", "Sumacab", "Active"],
    [2, "focal@sumacab.cict.edu.ph", "09123456781", "CICT", "Sumacab", "Active"],
    [3, "ret@sumacab.cict.edu.ph", "09123456782", "CICT", "Sumacab", "Active"],
    [4, "res@sumacab.cict.edu.ph", "09123456783", "CICT", "Sumacab", "Active"],
    [5, "ta@sumacab.cict.edu.ph", "09123456784", "CICT", "Sumacab", "Active"],
    [6, "panel@sumacab.cict.edu.ph", "09123456785", "CICT", "Sumacab", "Active"],

    // Rest of employees
    [7, "jaysonrivera@gmail.com", "09123456789", "CICT", "Sumacab", "Active"],
    [8, "ashleybathan@gmail.com", "09876454321", "CICT", "Sumacab", "Active"],
    [9, "henreichcatig@gmail.com", "0987123456", "CICT", "Sumacab", "Active"],
    [10, "ivankylesmaniego@gmail.com", "09123876548", "CICT", "Sumacab", "Active"],
    [11, "almagalang@gmail.com", "0998653231", "COE", "Fort Magsaysay", "Active"],
    [12, "kobegarcia@gmail.com", "09123456781", "NTP", "Fort Magsaysay", "Active"],
    [13, "mariaclara@gmail.com", "09112223344", "CICT", "Sumacab", "Active"],
    [14, "juancruz@gmail.com", "09998887766", "CICT", "Sumacab", "Active"],
    [15, "pedrosantos@gmail.com", "09115556677", "CICT", "Sumacab", "Active"],
    [16, "ana.delacruz@gmail.com", "09223334455", "CICT", "Sumacab", "Active"],
    [17, "roberto.diaz@gmail.com", "09331112222", "CICT", "Sumacab", "Active"],
    [18, "katrina.luz@gmail.com", "09117778899", "CICT", "San Isidro", "Active"],
    [19, "mark.estrada@gmail.com", "09887776655", "CICT", "Sumacab", "Active"],
    [20, "sofia.mendoza@gmail.com", "09112227788", "CICT", "Sumacab", "Active"],
    [21, "angelo.santos@gmail.com", "09995554433", "CICT", "Sumacab", "Active"],
    [22, "angelo.santos@gmail.com", "09995554433", "CICT", "Sumacab", "Active"],
    [23, "marie.delacruz@gmail.com", "09178889900", "CoEd", "Gen. Tinio", "Active"],
    [24, "carl.mendoza@yahoo.com", "09223334455", "Crim", "San Isidro", "Inactive"],
    [25, "jessica.ramos@outlook.com", "09776665544", "CIT", "Atate", "Active"],
    [26, "patrick.garcia@gmail.com", "09457778822", "CPADM", "Gabaldon", "Active"],
    [27, "daniel.lopez@gmail.com", "09351234567", "CMBT", "Fort Magsaysay", "Active"],
    [28, "samantha.cruz@yahoo.com", "09681234567", "CON", "Sumacab", "Active"],
    [29, "john.reyes@outlook.com", "09991231234", "GS", "Gen. Tinio", "Active"],
    [30, "kristine.hernandez@gmail.com", "09778889911", "LHS", "Gabaldon", "Inactive"],
    [31, "andrew.flores@yahoo.com", "09556667788", "CAS", "Atate", "Active"],
    [32, "michelle.gonzales@gmail.com", "09442223344", "COE", "San Isidro", "Active"],
    [33, "vincent.rodriguez@outlook.com", "09192221100", "IOLL", "Fort Magsaysay", "Active"],
    [34, "charlotte.morales@gmail.com", "09221113322", "IPE", "Sumacab", "Active"],
    [35, "miguel.torres@yahoo.com", "09998887766", "CoArch", "Gen. Tinio", "Active"],
    [36, "angelica.navarro@gmail.com", "09773334455", "NTP", "Gabaldon", "Active"],
    [37, "justin.martinez@outlook.com", "09669998877", "CICT", "Atate", "Inactive"],
    [38, "rebecca.villanueva@gmail.com", "09334445566", "CAS", "Sumacab", "Active"],
    [39, "rafael.dominguez@yahoo.com", "09115556677", "COE", "San Isidro", "Active"],
    [40, "melissa.padilla@outlook.com", "09558889900", "CIT", "Fort Magsaysay", "Active"],
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
        "email" => "admin@sumacab.cict.edu.ph",
        "pass" => password_hash("admin", PASSWORD_DEFAULT),
        "fname" => "Juan",
        "lname" => "Dela Cruz",
        "position" => "Director",
        "department" => "CICT",
        "campus" => "Sumacab"
    ],
    [
        "id" => 2,
        "email" => "focal@sumacab.cict.edu.ph",
        "pass" => password_hash("focal", PASSWORD_DEFAULT),
        "fname" => "Maria",
        "lname" => "Santos",
        "position" => "Focal Person",
        "department" => "CICT",
        "campus" => "Sumacab"
    ],
    [
        "id" => 3,
        "email" => "ret@sumacab.cict.edu.ph",
        "pass" => password_hash("ret", PASSWORD_DEFAULT),
        "fname" => "Jose",
        "lname" => "Ramos",
        "position" => "RET Chair",
        "department" => "CICT",
        "campus" => "Sumacab"
    ],
    [
        "id" => 4,
        "email" => "res@sumacab.cict.edu.ph",
        "pass" => password_hash("res", PASSWORD_DEFAULT),
        "fname" => "Ana",
        "lname" => "Lopez",
        "position" => "Researcher",
        "department" => "CICT",
        "campus" => "Sumacab"
    ],
    [
        "id" => 5,
        "email" => "ta@sumacab.cict.edu.ph",
        "pass" => password_hash("ta", PASSWORD_DEFAULT),
        "fname" => "Mark",
        "lname" => "Villanueva",
        "position" => "Technical Assistant",
        "department" => "CICT",
        "campus" => "Sumacab"
    ],
    [
        "id" => 6,
        "email" => "panel@sumacab.cict.edu.ph",
        "pass" => password_hash("panel", PASSWORD_DEFAULT),
        "fname" => "Carla",
        "lname" => "Reyes",
        "position" => "Panel",
        "department" => "CICT",
        "campus" => "Sumacab"
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
    // IDs 1–6 (also in accounts_tbl)
    [1, "Juan", "D", "Dela Cruz", "Mabini, Cabanatuan City", "1985-01-15", "Married", "Male", "Male", "None", "L", "40000-50000", 1, 2, "No concern"],
    [2, "Maria", "S", "Santos", "Santos Street, Cabanatuan City", "1990-05-22", "Single", "Female", "Female", "None", "M", "30000-40000", 2, 0, "N/A"],
    [3, "Jose", "R", "Ramos", "Ramos Avenue, Cabanatuan City", "1988-09-10", "Married", "Male", "Male", "PWD", "XL", "Below 10000", 3, 1, "Medical support needed"],
    [4, "Ana", "L", "Lopez", "Lopez Street, Cabanatuan City", "1992-12-05", "Single", "Female", "Female", "Senior Citizen", "M", "10000-30000", 4, 0, "N/A"],
    [5, "Mark", "V", "Villanueva", "Villanueva St., Sumacab", "1987-07-18", "Married", "Male", "Male", "None", "L", "30000-40000", 5, 2, "Needs training support"],
    [6, "Carla", "R", "Reyes", "Reyes Ave., Sumacab", "1991-03-30", "Single", "Female", "Female", "None", "S", "Below 10000", 6, 0, "N/A"],

    // IDs 7–40 (only employee_info_tbl)
    [7, "Jayson", "R", "Rivera", "Sumacab St., Cabanatuan City", "1999-10-10", "Single", "Male", "Male", "PWD", "L", "Below 10000", 7, 0, "No concern"],
    [8, "Ashley", "A", "Bathan", "Bathan Street, Sumacab", "1998-04-01", "Single", "Female", "Female", "None", "M", "10000-30000", 8, 0, "N/A"],
    [9, "Henreich", "L", "Catig", "Kapitan Pepe St., Sumacab", "1995-04-09", "Widowed", "Male", "Male", "Senior Citizen", "M", "10000-30000", 9, 2, "Needs medical assistance"],
    [10, "Ivan Kyle", "S", "Samaniego", "Zulueta St., Sumacab", "1994-04-25", "Married", "Male", "Male", "Senior Citizen", "L", "40000-50000", 10, 1, "Wants training program"],
    [11, "Alma", "G", "Galang", "Gen. Tinio, Nueva Ecija", "1993-04-28", "Married", "Female", "Female", "None", "4XL", "30000-40000", 11, 3, "Looking for livelihood support"],
    [12, "Kobe", "A", "Garcia", "Santa Rosa, Nueva Ecija", "2000-12-12", "Single", "Male", "Male", "PWD", "2XL", "Below 10000", 12, 0, "No concern"],
    [13, "Maria", "D", "Del Rosario", "Fort Magsaysay", "1997-07-07", "Single", "Female", "Female", "None", "M", "10000-30000", 13, 0, "N/A"],
    [14, "Juan", "R", "Reyes", "Sumacab", "1995-01-12", "Married", "Male", "Male", "None", "L", "30000-40000", 14, 1, "N/A"],
    [15, "Pedro", "M", "Santos", "Atate", "1996-02-14", "Single", "Male", "Male", "PWD", "M", "Below 10000", 15, 0, "Medical concern"],
    [16, "Ana", "G", "Delacruz", "Gabaldon", "1998-03-21", "Single", "Female", "Female", "None", "S", "10000-30000", 16, 0, "N/A"],
    [17, "Roberto", "L", "Diaz", "Fort Magsaysay", "1993-05-10", "Married", "Male", "Male", "Senior Citizen", "XL", "40000-50000", 17, 2, "Needs livelihood support"],
    [18, "Katrina", "S", "Luz", "Sumacab", "1994-06-23", "Single", "Female", "Female", "None", "M", "10000-30000", 18, 0, "N/A"],
    [19, "Mark", "A", "Estrada", "Atate", "1995-07-15", "Married", "Male", "Male", "None", "L", "30000-40000", 19, 2, "No concern"],
    [20, "Sofia", "G", "Mendoza", "Gabaldon", "1996-08-19", "Single", "Female", "Female", "None", "S", "10000-30000", 20, 0, "N/A"],
    [21, "Angelo", "D", "Santos", "Fort Magsaysay", "1997-09-25", "Married", "Male", "Male", "PWD", "L", "Below 10000", 21, 1, "Medical assistance needed"],
    [22, "Marie", "S", "Delacruz", "Sumacab", "1998-10-30", "Single", "Female", "Female", "None", "M", "10000-30000", 22, 0, "N/A"],
    [23, "Carl", "P", "Mendoza", "Gen. Tinio", "1995-11-05", "Married", "Male", "Male", "Senior Citizen", "XL", "40000-50000", 23, 3, "Needs livelihood support"],
    [24, "Jessica", "R", "Ramos", "Atate", "1994-12-12", "Single", "Female", "Female", "None", "S", "10000-30000", 24, 0, "N/A"],
    [25, "Patrick", "L", "Garcia", "Gabaldon", "1993-01-15", "Married", "Male", "Male", "None", "L", "30000-40000", 25, 2, "N/A"],
    [26, "Daniel", "G", "Lopez", "Fort Magsaysay", "1992-02-20", "Single", "Male", "Male", "PWD", "XL", "Below 10000", 26, 0, "Medical concern"],
    [27, "Samantha", "M", "Cruz", "Sumacab", "1990-03-25", "Married", "Female", "Female", "None", "M", "30000-40000", 27, 1, "N/A"],
    [28, "John", "A", "Reyes", "Gen. Tinio", "1989-04-18", "Married", "Male", "Male", "None", "L", "40000-50000", 28, 2, "N/A"],
    [29, "Kristine", "L", "Hernandez", "Gabaldon", "1991-05-22", "Single", "Female", "Female", "Senior Citizen", "M", "10000-30000", 29, 0, "N/A"],
    [30, "Andrew", "G", "Flores", "Atate", "1993-06-10", "Married", "Male", "Male", "PWD", "XL", "Below 10000", 30, 1, "Medical assistance"],
    [31, "Michelle", "R", "Gonzales", "San Isidro", "1994-07-14", "Single", "Female", "Female", "None", "M", "10000-30000", 31, 0, "N/A"],
    [32, "Vincent", "T", "Rodriguez", "Fort Magsaysay", "1995-08-19", "Married", "Male", "Male", "None", "L", "30000-40000", 32, 2, "N/A"],
    [33, "Charlotte", "M", "Morales", "Sumacab", "1996-09-22", "Single", "Female", "Female", "PWD", "S", "Below 10000", 33, 0, "Medical concern"],
    [34, "Miguel", "L", "Torres", "Gen. Tinio", "1997-10-05", "Married", "Male", "Male", "None", "L", "30000-40000", 34, 1, "N/A"],
    [35, "Angelica", "D", "Navarro", "Gabaldon", "1998-11-11", "Single", "Female", "Female", "None", "M", "10000-30000", 35, 0, "N/A"],
    [36, "Justin", "R", "Martinez", "Atate", "1999-12-02", "Married", "Male", "Male", "Senior Citizen", "XL", "40000-50000", 36, 2, "Needs livelihood support"],
    [37, "Rebecca", "S", "Villanueva", "Sumacab", "2000-01-19", "Single", "Female", "Female", "None", "M", "10000-30000", 37, 0, "N/A"],
    [38, "Rafael", "A", "Dominguez", "San Isidro", "1998-02-14", "Married", "Male", "Male", "PWD", "L", "Below 10000", 38, 1, "Medical assistance"],
    [39, "Melissa", "G", "Padilla", "Fort Magsaysay", "1997-03-07", "Single", "Female", "Female", "None", "S", "10000-30000", 39, 0, "N/A"],
    [40, "Gabriel", "R", "Rivera", "Gabaldon", "1996-04-21", "Married", "Male", "Male", "None", "L", "30000-40000", 40, 2, "N/A"],
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
        "issssssssssiiis", 
        $emp[0], $emp[1], $emp[2], $emp[3], $emp[4], $emp[5], $emp[6], $emp[7], $emp[8],
        $emp[9], $emp[10], $emp[11], $emp[12], $emp[13], $emp[14]
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
    ["CICT Foundation Day", "Celebration of the CICT department’s foundation.", "2025-09-01", "Event", null, null, null],
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
