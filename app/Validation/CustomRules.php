<?php

namespace App\Validation;

use App\Models\UserModel;


class CustomRules
{

  // Rule is to validate mobile number digits
  public function mobileValidation($mobile)
  {
    /*Checking: Número, tiene que empezar con 5-9{Resto Numbers}*/
    if (preg_match('/^[5-9]{1}[0-9]+/', $mobile)) {

      /*Checking: Mobile number must be of 9 digits*/
      $bool = preg_match('/^[0-9]{9}+$/', $mobile);
      return $bool == 0 ? false : true;
    } else {
      return false;
    }
  }

  public function ageValidation($age)
  {
    if ($age >= 18) return true;
    else return false;
  }



  public function emailUnicoUsr($email, $id)
  /**
   * Valida que un eMail sea único, excepto para el user con la id que se pasa como parámetro
   */
  {
    $user = new UserModel();
    $where = "email='$email' AND id <> '$id' ";
    $count = $user->where($where)->countAllResults();
    if ($count) return (false);
    return (true);
  }
}
