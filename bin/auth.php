<?php

function connectServer($data) {

    $server_id = explode(' ', $data)[0];

    if(!isset($_SESSION['loggedIn'])) { 
        if (file_exists("server/{$server_id}.json")) { 
            return "CONNECTING TO SERVER {$server_id}...";
        } else {
            return 'CONNECTION REFUSED';
        }
    }

    if(isset($_SESSION['loggedIn'])) {

        $username = $_SESSION['username'];

        if (file_exists("home/{$server_id}/{$username}")) {
            $_SESSION['home'] = realpath(HOME_DIRECTORY) . DIRECTORY_SEPARATOR . $server_id . DIRECTORY_SEPARATOR . $username; // Set user's directory
            $_SESSION['pwd'] = HOME_DIRECTORY . DIRECTORY_SEPARATOR . $server_id . DIRECTORY_SEPARATOR . $username; // Set user's directory
            return "CONNECTING TO SERVER {$server_id}...";
        } else {
            return 'CONNECTION TERMINATED';
        }
    }

    


}

function setupServer($data) {

    $server_id = explode(' ', $data)[0];

    $username = $_SESSION['username'];
    $password = $_SESSION['password'];
    
    if (!file_exists("server/{$server_id}.json")) {
        file_put_contents("server/{$server_id}.json", json_encode(
        [
                'name' => 'Default',
                'server' => $_SESSION['server_id'],
                'ip' => long2ip(mt_rand()),
                'root' => $username,
                'accounts' => [$username => $password],
                'blocked' => []
        ]
    ));
    } 
}

// Function to handle new user creation
function newUser($data) {
    global $server;

    $params = explode(' ', $data);

    // If no parameters provided, prompt for username
    if (empty($params)) {
        return "ERROR: USERNAME REQUIRED";
    }

    // Check if username already exists
    if (isset($server['accounts'][$params[0]])) {
        unset($_SESSION['newuser']); // Clear session data
        return "ERROR: USERNAME IN USE";
    }

    // If only username provided, store it in session and prompt for password
    if (count($params) === 1) {
        $username = $params[0];
        // Check if username is already set in session
        if (isset($_SESSION['newuser'])) {
            return "ENTER PASSWORD NOW: $username: ";
        } else {
            // Store username in session
            $_SESSION['newuser'] = $username;
            return "ENTER PASSWORD NOW: $username: ";
        }
    }

    // If both username and password provided, complete new user creation
    if (count($params) === 2) {
        $username = $_SESSION['newuser'];
        $password = $params[1];
        
        // Store the new user credentials
        $server['accounts'][$username] = $password;
        // Save the updated user data to the file
        file_put_contents("server/{$_SESSION['server_id']}.json", json_encode($server));
        // Create a folder for the new user
        $userFolder = HOME_DIRECTORY . $username;
        if (!file_exists($userFolder)) {
            mkdir($userFolder, 0777, true);
        }
        unset($_SESSION['newuser']); // Clear session data
        return "Welcome new user, {$username}";
    }

    return "ERROR: NEW_USER";
}

// Function to handle user login
function loginUser($data) {
    global $server, $server_id;

    $params = explode(' ', $data);

    // If no parameters provided, prompt for username
    if (empty($params)) {
        return "ERROR: WRONG USERNAME";
    } else {
        $username = $params[0];
    }

    if(!isset($server['accounts'][$username])) {
        return "ERROR: WRONG USERNAME";
    }


    // If both username and password provided, complete login process
    if (count($params) === 2) {
        $username = $params[0];
        $password = $params[1];

        // Validate password
        if (isset($server['accounts'][$username]) && $server['accounts'][$username] === $password) {
            $_SESSION['loggedIn'] = true;
            $_SESSION['username'] = $username;
            $_SESSION['password'] = $password;
            $_SESSION['home'] = realpath(HOME_DIRECTORY) . DIRECTORY_SEPARATOR . $server_id . DIRECTORY_SEPARATOR . $username; // Set user's directory
            $_SESSION['pwd'] = HOME_DIRECTORY . DIRECTORY_SEPARATOR . $server_id . DIRECTORY_SEPARATOR . $username; // Set user's directory
            
            $userFolder = $_SESSION['home'];
            if (!file_exists($userFolder)) {
                mkdir($userFolder, 0777, true);
            }

            if(!isset($_SESSION['server_id'])) {
                $_SESSION['server_id'] = $server_id ;
            }
            
            unset($_SESSION['loginUser']); // Remove temporary session variable
            return "LOGGING IN...";

        } else {
            unset($_SESSION['loginUser']); // Remove temporary session variable
            return "ERROR: WRONG PASSWORD"; // Invalid password message
        }
    }

    return "ERROR: WRONG INPUT"; // Invalid login parameters message
}

// Function to handle whoami command
function whoAmI() {
    if (isset($_SESSION['username'])) {
        return $_SESSION['username']; // Return the logged-in user's username
    } else {
        return "ERROR: LOGON REQUIRED"; // Return a message indicating not logged in
    }
}

// Function to handle user logout
function logoutUser() {
    $_SESSION = array();
    session_destroy();
    return "LOGGING OUT...";
}