
rem RunP.bat
rem 同时并行运行多个程序

set cmd1=php comp_csv.php 0 50000
set cmd2=php comp_csv.php 49990 100000
set cmd3=php comp_csv.php 99990 150000
set cmd4=php comp_csv.php 149990 200000
set cmd5=php comp_csv.php 199990 250000
set cmd6=php comp_csv.php 249990 300000
set cmd7=php comp_csv.php 299990 350000
set cmd8=php comp_csv.php 349990 400000
set cmd9=php comp_csv.php 399990 450000
set cmd10=php comp_csv.php 449990 500000
set cmd11=php comp_csv.php 499990 550000
set cmd12=php comp_csv.php 549990 600000
set cmd13=php comp_csv.php 599990 650000
set cmd14=php comp_csv.php 649990 700000
set cmd15=php comp_csv.php 699990 750000
set cmd16=php comp_csv.php 749990 800000
set cmd17=php comp_csv.php 799990 855000

start %cmd1%
start %cmd2%
start %cmd3%
start %cmd4%
start %cmd5%
start %cmd6%
start %cmd7%
start %cmd8%
start %cmd9%
start %cmd10%
start %cmd11%
start %cmd12%
start %cmd13%
start %cmd14%
start %cmd15%
start %cmd16%
start %cmd17%
