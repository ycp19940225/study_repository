<?php

fwrite(STDOUT, "输入代码根目录: ");

$dir = trim(fgets(STDIN));

$root = 'E:/phpstudy_pro/WWW/';

$baseDir = $root.$dir;

if(!file_exists($baseDir)){
    echo '当前项目不存在';
    $check = true;
    while ($check){
        fwrite(STDOUT, "\n输入代码根目录: ");
        $dir = trim(fgets(STDIN));
        $baseDir = $root.$dir;
        if(file_exists($baseDir)){
            $check = false;
        }else{
            echo "当前项目不存在";
        }
    }
}


$controlTemplate = './formwork/swoft-service-base/app/Http/Controller/DemoController.php';
$logicTemplate = './formwork/swoft-service-base/app/Model/Logic/DemoLogic.php';

$viewTemplate[] = './formwork/swoft-service-base/resource/demo/index.php';
$viewTemplate[] = './formwork/swoft-service-base/resource/demo/form_add.php';
$viewTemplate[] = './formwork/swoft-service-base/resource/demo/form_edit.php';

$tempDir = './temp/temp.php';
copy($controlTemplate, $tempDir);

fwrite(STDOUT, "输入模块名: ");
$module = trim(fgets(STDIN));
$controllerContent = file_get_contents($tempDir);

$class = ucfirst($module);
$controllerContentTemp = str_replace('Demo', $class, $controllerContent);
$controllerContentTemp = str_replace('demo', strtolower($module), $controllerContentTemp);

$controllerDir = "$baseDir\app\Http\Controller\Admin";
$file = "$controllerDir/$class"."Controller.php";
$path = dirname($file);
if(!file_exists($path)){
    mkdir($path, '0777', true);
}
file_put_contents($file, $controllerContentTemp);


// model
copy($logicTemplate, $tempDir);
$logicContent = file_get_contents($tempDir);

$logicContentContentTemp = str_replace('Demo', ucfirst($module), $logicContent);
$logicContentContentTemp = str_replace('demo', strtolower($module), $logicContentContentTemp);

$modelDir = "$baseDir\app\Model\Logic";
$file = "$modelDir/$class"."Logic.php";
$path = dirname($file);
if(!file_exists($path)){
    mkdir($path, '0777', true);
}
file_put_contents($file, $logicContentContentTemp);

fwrite(STDOUT, "表单类型（1弹窗2页面）: ");
$formType = trim(fgets(STDIN));

if($formType == 1){
    foreach ($viewTemplate as $item){
        copy($item, $tempDir);
        $viewContent = file_get_contents($tempDir);

        $viewContentTemp = str_replace('Demo', ucfirst($module), $viewContent);
        $viewContentTemp = str_replace('demo', strtolower($module), $viewContentTemp);

        $viewDir = "$baseDir\/resource";
        $fileName = explode('/', $item);
        $fileName = array_pop($fileName);
        $file = "$viewDir/$module/$fileName";
        $path = dirname($file);
        if(!file_exists($path)){
            mkdir($path, '0777', true);
        }
        file_put_contents($file, $viewContentTemp);
        break;
    }
}else{
    foreach ($viewTemplate as $item){
        copy($item, $tempDir);
        $viewContent = file_get_contents($tempDir);

        $viewContentTemp = str_replace('Demo', ucfirst($module), $viewContent);
        $viewContentTemp = str_replace('demo', strtolower($module), $viewContentTemp);

        $viewDir = "$baseDir\/resource";
        $fileName = explode('/', $item);
        $fileName = array_pop($fileName);
        $file = "$viewDir/$module/$fileName";
        $path = dirname($file);
        if(!file_exists($path)){
            mkdir($path, '0777', true);
        }
        file_put_contents($file, $viewContentTemp);
    }
}




