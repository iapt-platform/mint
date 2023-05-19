1. 建立表
php artisan make:model Lesson -m

2. 修改迁移文件，添加列


3. 升级数据库
php artisan migrate

4. 降级

php artisan migrate:rollback --step=1


5. 升级数据库
php artisan migrate

6. 建立控制器

php artisan make:controller --api --model=<model name>  <model name>Controller

如： model = Lesson
php artisan make:controller --api --model=Lesson  LessonController

1. 数据库查询

        $table = new Tag();
        $table = $table->where('name','aaa');
        $count = $table->count();
        // select count(*) where name = 'aaa';

        $data = $table->select(['name','id'])->get();
        // select name,id where name = 'aaa';
        return $this->ok($data);

        /*
        ['rows'=>$data,'count'=>$count]
        {
            rows: data,
            count: count,
        }
        */

		/**
 * GET tag/ -> index()
 * GET tag/:id -> show()
 * POST tag  -> store()
 * PUT tag/:id -> update()
 * DELETE tag/:id -> destroy()
 */

前端 api 配置

.env

 REACT_APP_API_HOST=http://127.0.0.1:8000/api/v2

前端调用



