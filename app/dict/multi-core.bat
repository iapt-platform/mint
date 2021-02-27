
rem RunP.bat
rem 同时并行运行多个程序
set cmd1=php comp_csv.php 0 20000
set cmd2=php comp_csv.php 20001 40000
start %cmd1%
start %cmd2%