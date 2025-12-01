<?php
// Handle POST for deletion of user in admin page

function handle_user_deletion() {
    $user_id_to_delete = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
    // Prevent admin from deleting themselves
    if ($user_id_to_delete === $_SESSION['user_id']) {
        $result_msg = ['type'=>'error','text'=>'Du kan ikke slette din egen bruker!.'];
    } else {
        $result_msg = ['type'=>'success','text'=>'Bruker slettet.'];
        deleteUser($user_id_to_delete);
    }
    // Set message and redirect
    $_SESSION['status_message'] = $result_msg;
    header('Location: admin.php');
    exit;
}

?>