<?php 
require_once 'connect.php';?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equive="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width",
    intial-scale="1.0">
    <title>User</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css">

    <style>
      h1 {
        text-align: center;
      }
      body {
            background-color: #e0e0e0;
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            color: white; /* Text color to contrast with the background */
        }

        .container {
            background-color: rgba(0, 0, 0, 0.7); /* Adds transparency behind the content */
            padding: 20px;
            border-radius: 10px;
        }
    </style>
</head>
<body>

<div class="container">
<button class="btn btn-primary my-5"><a href="update_users.php"class="text-light">Add user</a>
</button>
<button class="btn btn-primary my-5"><a href="index3.php"class="text-light">Sign out</a>
</button>
<h1>Manage User</h1><br>

<table class="table table-striped table-dark">
  <thead>
    <tr>
      <th scope="Sl no">#</th>
      <th scope="col">Name</th>
      <th scope="col">Email</th>
      <th scope="col">Mobile</th>
      <th scope="col">Password</th>
      <th scope="col">Role</th>
      <th scope="col">Operations</th>
    </tr>
  </thead>
  <tbody>

  <?php

$sql="Select * from user";
$result=mysqli_query($con,$sql);

if($result){
    while($row=mysqli_fetch_assoc($result)){
        $id=$row['id'];
        $name=$row['name'];
        $email=$row['email'];
        $mobile=$row['mobile'];
        $password=$row['password'];
        $role=$row['role'];
        echo ' <tr>
        <th scope="row">'.$id.'</th>
        <td>'.$name.'</td>
        <td>'.$email.'</td>
        <td>'.$mobile.'</td>
        <td>'.$password.'</td>
        <td>'.$role. '</td>
        <td>
    <button class="btn btn-primary"><a href="update.php?
    updateid='.$id.'" class="text-light">Update</a></button>
    <button class="btn btn-danger"><a href="delete.php?
    deleteid='.$id.'"
    class="text-light">Delete</a></button>
    </td>
      </tr>';
    }
}



?>

    
  </tbody>
</table>
<div class="text-center mt-4">
        <a href="admin_dashboard.php" class="btn btn-secondary">Back to Admin Dashboard</a>
    </div>
</div>
</div>

</body>
</html>