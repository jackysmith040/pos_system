<?php
session_start();
ini_set('display_errors', 1);
class Action
{
	private $db;

	public function __construct()
	{
		ob_start();
		include('db_connect.php');

		$this->db = $conn;
	}
	function __destruct()
	{
		$this->db->close();
		ob_end_flush();
	}

	function login()
	{
		extract($_POST);
		$qry = $this->db->query("SELECT * FROM users WHERE username = '" . $username . "' AND password = '" . md5($password) . "' ");

		if ($qry->num_rows > 0) {
			$user = $qry->fetch_array();
			// Store user information in the session
			foreach ($user as $key => $value) {
				if ($key != 'password' && !is_numeric($key)) // Ensure to exclude the password
					$_SESSION['login_' . $key] = $value;
			}

			return json_encode(['success' => true, 'userType' => $user['type']]); // Return user type
		} else {
			return json_encode(['success' => false]); // Login failed
		}
	}

	function login2()
	{

		extract($_POST);
		$qry = $this->db->query("SELECT * FROM complainants where email = '" . $email . "' and password = '" . md5($password) . "' ");
		if ($qry->num_rows > 0) {
			foreach ($qry->fetch_array() as $key => $value) {
				if ($key != 'passwors' && !is_numeric($key))
					$_SESSION['login_' . $key] = $value;
			}
			return 1;
		} else {
			return 3;
		}
	}
	function logout()
	{
		session_destroy();
		foreach ($_SESSION as $key => $value) {
			unset($_SESSION[$key]);
		}
		header("location:login.php");
	}
	function logout2()
	{
		session_destroy();
		foreach ($_SESSION as $key => $value) {
			unset($_SESSION[$key]);
		}
		header("location:../index.php");
	}

	function save_user()
	{
		extract($_POST);
		$data = " name = '$name' ";
		$data .= ", username = '$username' ";
		if (!empty($password))
			$data .= ", password = '" . md5($password) . "' ";
		$data .= ", type = '$type' ";
		$chk = $this->db->query("Select * from users where username = '$username' and id !='$id' ")->num_rows;
		if ($chk > 0) {
			return 2;
			exit;
		}
		if (empty($id)) {
			$save = $this->db->query("INSERT INTO users set " . $data);
		} else {
			$save = $this->db->query("UPDATE users set " . $data . " where id = " . $id);
		}
		if ($save) {
			return 1;
		}
	}
	function delete_user()
	{
		extract($_POST);
		$delete = $this->db->query("DELETE FROM users where id = " . $id);
		if ($delete)
			return 1;
	}
	function signup()
	{
		extract($_POST);
		$data = " name = '$name' ";
		$data .= ", email = '$email' ";
		$data .= ", address = '$address' ";
		$data .= ", contact = '$contact' ";
		$data .= ", password = '" . md5($password) . "' ";
		$chk = $this->db->query("SELECT * from complainants where email ='$email' " . (!empty($id) ? " and id != '$id' " : ''))->num_rows;
		if ($chk > 0) {
			return 3;
			exit;
		}
		if (empty($id))
			$save = $this->db->query("INSERT INTO complainants set $data");
		else
			$save = $this->db->query("UPDATE complainants set $data where id=$id ");
		if ($save) {
			if (empty($id))
				$id = $this->db->insert_id;
			$qry = $this->db->query("SELECT * FROM complainants where id = $id ");
			if ($qry->num_rows > 0) {
				foreach ($qry->fetch_array() as $key => $value) {
					if ($key != 'password' && !is_numeric($key))
						$_SESSION['login_' . $key] = $value;
				}
				return 1;
			} else {
				return 3;
			}
		}
	}
	function update_account()
	{
		extract($_POST);
		$data = " name = '" . $firstname . ' ' . $lastname . "' ";
		$data .= ", username = '$email' ";
		if (!empty($password))
			$data .= ", password = '" . md5($password) . "' ";
		$chk = $this->db->query("SELECT * FROM users where username = '$email' and id != '{$_SESSION['login_id']}' ")->num_rows;
		if ($chk > 0) {
			return 2;
			exit;
		}
		$save = $this->db->query("UPDATE users set $data where id = '{$_SESSION['login_id']}' ");
		if ($save) {
			$data = '';
			foreach ($_POST as $k => $v) {
				if ($k == 'password')
					continue;
				if (empty($data) && !is_numeric($k))
					$data = " $k = '$v' ";
				else
					$data .= ", $k = '$v' ";
			}
			if ($_FILES['img']['tmp_name'] != '') {
				$fname = strtotime(date('y-m-d H:i')) . '_' . $_FILES['img']['name'];
				$move = move_uploaded_file($_FILES['img']['tmp_name'], 'assets/uploads/' . $fname);
				$data .= ", avatar = '$fname' ";

			}
			$save_alumni = $this->db->query("UPDATE alumnus_bio set $data where id = '{$_SESSION['bio']['id']}' ");
			if ($data) {
				foreach ($_SESSION as $key => $value) {
					unset($_SESSION[$key]);
				}
				$login = $this->login2();
				if ($login)
					return 1;
			}
		}
	}

	function save_settings()
	{
		// Initialize data array
		$data = [];

		// Check and assign POST data safely
		$data['name'] = isset($_POST['name']) ? str_replace("'", "&#x2019;", $_POST['name']) : '';
		$data['email'] = isset($_POST['email']) ? $_POST['email'] : ''; // Assuming email is safe or already validated
		$data['contact'] = isset($_POST['contact']) ? $_POST['contact'] : ''; // Assuming contact is safe or already validated
		$data['about_content'] = isset($_POST['about']) ? htmlentities(str_replace("'", "&#x2019;", $_POST['about'])) : '';

		// Add new fields for VAT, discount, COVID tax, and footer message
		$data['vat_percentage'] = isset($_POST['vat_percentage']) ? $_POST['vat_percentage'] : null;
		$data['covid_tax_percentage'] = isset($_POST['covid_tax_percentage']) ? $_POST['covid_tax_percentage'] : null;
		$data['default_discount'] = isset($_POST['default_discount']) ? $_POST['default_discount'] : null;
		$data['footer_message'] = isset($_POST['footer_message']) ? str_replace("'", "&#x2019;", $_POST['footer_message']) : '';

		// Handle cover image upload if provided
		if (isset($_FILES['img']) && $_FILES['img']['tmp_name'] != '') {
			$fname = strtotime(date('y-m-d H:i')) . '_' . $_FILES['img']['name'];
			$move = move_uploaded_file($_FILES['img']['tmp_name'], 'assets/uploads/' . $fname);
			if ($move) {
				$data['cover_img'] = $fname; // Only add if the move was successful
			}
		}

		// Prepare the SQL query
		$set_clause = [];
		foreach ($data as $key => $value) {
			if ($value !== null) { // Only include non-null values in the query
				$set_clause[] = "$key = '" . $this->db->escape_string($value) . "'"; // Use escape_string to prevent SQL injection
			}
		}

		// Save or update the settings in the database
		$chk = $this->db->query("SELECT * FROM system_settings");
		if ($chk->num_rows > 0) {
			$save = $this->db->query("UPDATE system_settings SET " . implode(", ", $set_clause));
		} else {
			$save = $this->db->query("INSERT INTO system_settings SET " . implode(", ", $set_clause));
		}

		// Update the session with the latest settings values
		if ($save) {
			$query = $this->db->query("SELECT * FROM system_settings LIMIT 1")->fetch_array();
			foreach ($query as $key => $value) {
				if (!is_numeric($key)) {
					$_SESSION['system'][$key] = $value;
				}
			}
			return 1; // Return 1 for success
		} else {
			return 0; // Return 0 for error
		}
	}



	function save_category()
	{
		extract($_POST);
		$data = "";
		foreach ($_POST as $k => $v) {
			if (!in_array($k, array('id')) && !is_numeric($k)) {
				if (empty($data)) {
					$data .= " $k='$v' ";
				} else {
					$data .= ", $k='$v' ";
				}
			}
		}
		$check = $this->db->query("SELECT * FROM categories where name ='$name' " . (!empty($id) ? " and id != {$id} " : ''))->num_rows;
		if ($check > 0) {
			return 2;
			exit;
		}
		if (empty($id)) {
			$save = $this->db->query("INSERT INTO categories set $data");
		} else {
			$save = $this->db->query("UPDATE categories set $data where id = $id");
		}

		if ($save)
			return 1;
	}
	function delete_category()
	{
		extract($_POST);
		$delete = $this->db->query("DELETE FROM categories where id = " . $id);
		if ($delete) {
			return 1;
		}
	}
	function save_product()
{
	extract($_POST);
	$data = "";
	foreach ($_POST as $k => $v) {
		if (!in_array($k, array('id', 'status', 'stock')) && !is_numeric($k)) { // Exclude 'stock' from foreach
			if ($k == 'price') {
				$v = str_replace(',', '', $v);
			}
			if (empty($data)) {
				$data .= " $k='$v' ";
			} else {
				$data .= ", $k='$v' ";
			}
		}
	}

	// Handle stock separately
	if (isset($stock) && is_numeric($stock)) {
		$data .= ", stock='$stock' ";
	} else {
		$data .= ", stock=0 "; // Default to 0 if stock is not provided or invalid
	}

	if (isset($status)) {
		$data .= ", status=1 ";
	} else {
		$data .= ", status=0 ";
	}

	// Check for duplicate product name, excluding current product ID if editing
	$check = $this->db->query("SELECT * FROM products WHERE name ='$name' " . (!empty($id) ? " AND id != {$id} " : ''))->num_rows;
	if ($check > 0) {
		return 2;
		exit;
	}

	// Insert or update the product record
	if (empty($id)) {
		$save = $this->db->query("INSERT INTO products SET $data");
	} else {
		$save = $this->db->query("UPDATE products SET $data WHERE id = $id");
	}

	if ($save)
		return 1;
}


	function delete_product()
	{
		extract($_POST);
		$delete = $this->db->query("DELETE FROM products where id = " . $id);
		if ($delete) {
			return 1;
		}
	}
	
	function save_order()
{
    extract($_POST);

    // Capture payment method from POST request
    $payment_method = isset($_POST['payment_method']) ? $_POST['payment_method'] : '';

    // Begin data string with existing fields
    $data = " total_amount = '$total_amount' ";
    $data .= ", amount_tendered = '$total_tendered' ";
    $data .= ", order_number = '$order_number' ";
    $data .= ", payment_method = '$payment_method' "; // Add payment method to data string

    if (empty($id)) {
        // Generate unique reference number
        $i = 0;
        while ($i == 0) {
            $ref_no = mt_rand(1, 999999999999);
            $ref_no = sprintf("%'012d", $ref_no);
            $chk = $this->db->query("SELECT * FROM sales where ref_no ='$ref_no' ");
            if ($chk->num_rows <= 0) {
                $i = 1;
            }
        }
        $data .= ", ref_no = '$ref_no' ";

        // Insert new record
        $stmt = $this->db->prepare("INSERT INTO sales SET total_amount = ?, amount_tendered = ?, order_number = ?, payment_method = ?, ref_no = ?");
        $stmt->bind_param('ddsss', $total_amount, $total_tendered, $order_number, $payment_method, $ref_no);
        $save = $stmt->execute();
        if ($save) {
            $id = $this->db->insert_id;
        }
    } else {
        // Update existing record
        $stmt = $this->db->prepare("UPDATE sales SET total_amount = ?, amount_tendered = ?, order_number = ?, payment_method = ? WHERE id = ?");
        $stmt->bind_param('ddssi', $total_amount, $total_tendered, $order_number, $payment_method, $id);
        $save = $stmt->execute();
    }

    if ($save) {
        $ids = array_filter($item_id);
        $ids = implode(',', $ids);
        if (!empty($ids)) {
            $this->db->query("DELETE FROM sale_items WHERE order_id = $id AND id NOT IN ($ids)");
        }
        foreach ($item_id as $k => $v) {
            $data = " order_id = $id ";
            $data .= ", product_id = '{$product_id[$k]}' ";
            $data .= ", qty = '{$qty[$k]}' ";
            $data .= ", price = '{$price[$k]}' ";
            $data .= ", amount = '{$amount[$k]}' ";
            if (empty($v)) {
                $this->db->query("INSERT INTO sale_items SET $data");
            } else {
                $this->db->query("UPDATE sale_items SET $data WHERE id = $v");
            }
        }

        // Step: Trigger `receipt_logic.php` with the saved order ID
        // Using cURL to trigger the receipt logic
        $url = "/receipt_logic.php";
        $postData = ['sale_id' => $id];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_exec($ch);
        curl_close($ch);

        return $id;
    }
}


	function delete_order()
	{
		extract($_POST);
		$delete = $this->db->query("DELETE FROM sales where id = " . $id);
		$delete2 = $this->db->query("DELETE FROM sale_items where order_id = " . $id);
		if ($delete) {
			return 1;
		}
	}
}
