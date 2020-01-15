<?php


class CoreServerManager
{
    public function createDirectory($gid, $pi, $user){
        $safeGid = escapeshellarg($gid);
        $safePi = escapeshellarg($pi);
        $safeUser = escapeshellarg($user);
        exec("sudo addCoreServerDir.sh $safeGid $safePi $safeUser", $shellOut);
    }
}