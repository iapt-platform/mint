
rem RunP.bat
rem 同时并行运行多个程序

set cmd1=php comp_csv.php 1 2000
set cmd2=php comp_csv.php 2001 4000
set cmd3=php comp_csv.php 4001 6000
set cmd4=php comp_csv.php 6001 8000
set cmd5=php comp_csv.php 8001 10000
set cmd6=php comp_csv.php 10001 12000

start %cmd1%
start %cmd2%
start %cmd3%
start %cmd4%
start %cmd5%
start %cmd6%