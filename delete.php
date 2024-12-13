<?php
require_once 'connect.php';
if(isset($_GET['deleteid'])){
    $id=$_GET['deleteid'];

    $sql="delete from `user` where id=$id";
    $result=mysqli_query($con,$sql);
    if($result){
      //  echo "Deleted successfull";
      header('location:display_user.php');
    }else{
        die(mysqli_error($con));
    }

}

?>