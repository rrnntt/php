<?php
/*
Connect to the database.
*/
$con = mysql_connect("localhost","tolchenov_com","aNrSwbGG");
if (!$con)
  {
    die('Could not connect: ' . mysql_error());
    //die('Database error.');
  }

mysql_select_db("tolchenov_com", $con);

/**
 * Return a list of all users.
 */
function users()
{
 $result = mysql_query("SELECT * FROM users");
 $userArray = array();
 if($result)
 {
   while($row = mysql_fetch_array($result))
   {
      array_push($userArray,$row);
   }
 }
 else
 { 
  echo 'empty';
 }
 return $userArray;
}

/**
 * Get user with name $name. A user is a map: 'db table field' -> value
 */
function user($name)
{
  $result = mysql_query("SELECT * FROM users WHERE username='".$name."'");
  if ($result)
  {
    $row = mysql_fetch_array($result);
    return $row;
  }
  return array();
}

?> 
