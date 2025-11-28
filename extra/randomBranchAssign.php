<?php

    include("dbConnection.php");
     
    $kannadaBranch = [];
    $teluguBranch = [];
    $tamilBranch = [];
    $branchQuery = mysqli_query($con, "SELECT branchId, state FROM branch WHERE status=1 AND branchId!='AGPL000'");
    while($row = mysqli_fetch_assoc($branchQuery)){
        if($row['state'] == 'Karnataka'){ 
            $kannadaBranch[$row['branchId']] = "";       
        }
        else if($row['state'] == 'Andhra Pradesh' || $row['state'] == 'Telangana'){
            $teluguBranch[$row['branchId']] = "";
        }
        else if($row['state'] == 'Tamilnadu' || $row['state'] == 'Pondicherry'){
            $tamilBranch[$row['branchId']] = "";
        }
    }
    
    $kannadaVM = [];
    $teluguVM = [];
    $tamilVM = [];
    $vmQuery = mysqli_query($con, "SELECT agentId, grade FROM vmagent WHERE grade!=''");
    while($row = mysqli_fetch_assoc($vmQuery)){
        if($row['grade'] == 'A'){ 
            $kannadaVM[] = $row['agentId'];       
        }
        else if($row['grade'] == 'B'){
            $teluguVM[] = $row['agentId'];  
        }
        else if($row['grade'] == 'C'){
            $tamilVM[] = $row['agentId'];  
        }
    }

    shuffle($kannadaVM);
    $i = 0;
    $n = count($kannadaVM);
    foreach ($kannadaBranch as $key => $value) {
        $kannadaBranch[$key] = $kannadaVM[$i];
        $i = ($i < $n-1) ? $i+1 : 0;
    }
    
    shuffle($teluguVM);
    $i = 0;
    $n = count($teluguVM);
    foreach ($teluguBranch as $key => $value) {
        $teluguBranch[$key] = $teluguVM[$i];
        $i = ($i < $n-1) ? $i+1 : 0;
    }

    shuffle($tamilVM);
    $i = 0;
    $n = count($tamilVM);
    foreach ($tamilBranch as $key => $value) {
        $tamilBranch[$key] = $tamilVM[$i];
        $i = ($i < $n-1) ? ($i+1) : 0;
    }


    print_r($kannadaBranch);
    print_r($teluguBranch);
    print_r($tamilBranch);


?>
