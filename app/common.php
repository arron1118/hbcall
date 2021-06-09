<?php
// 应用公共文件

/**
 * 获取密码加密后的字符串
 * @param string $password 密码
 * @param string $salt 密码盐
 * @return string
 */
function getEncryptPassword($password, $salt = '')
{
    return md5(md5($password) . $salt);
}
