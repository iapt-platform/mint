package main

import (
	"github.com/gin-gonic/gin"
	"github.com/go-pg/pg/v10"
	"github.com/iapt-platform/mint"
	"fmt"
    "github.com/go-redis/redis/v8"
)

func main() {

	opt, err := pg.ParseURL("postgres://postgres:@127.0.0.1:5432/mint?sslmode=disable")
	if err != nil {
		panic(err)
	}
	fmt.Println("pg connectd")
	db := pg.Connect(opt)
	defer db.Close()

	rt := gin.Default()

	rdb := redis.NewClient(&redis.Options{
        Addr:     "localhost:6379",
        Password: "", // no password set
        DB:       0,  // use default DB
    })


	// TODO 在这里进行http mount

	rt.GET("/demo/user/:id", mint.GetDemo(db))
	//rt.POST("/demo/sign-in", mint.LoginDemo(db))
	rt.POST("/demo/user",mint.PostDemo(db))
	rt.PUT("/demo/user",mint.PutDemo(db))
	rt.PATCH("/demo/user/:id",mint.PatchDemo(db))
	rt.DELETE("/demo/user/:id",mint.DeleteDemo(db))

	//课程
	//根据id查询课程 
	rt.GET("/api/course/:cid",mint.GetCourse(db))
	//输入标题查询符合条件的课程 title% 
	rt.GET("/api/course/title/:ctitle",mint.GetCourseByTitle(db))
	//新建课程
	rt.PUT("/api/course",mint.PutCourse(db)) 
	//修改
	rt.POST("/api/course",mint.PostCourse(db))//改
	//删除
	rt.DELETE("/api/course/:cid",mint.DeleteCourse(db)) 
	//修改课程表里的课的数量
	rt.PATCH("/api/course/lessonnum",mint.PatchLessonNumInCousrse(db))

	//课
	//根据id查询课程
	rt.GET("/api/lesson/:lid",mint.GetLesson(db))
	//输入标题查询符合条件的课程 title% 
	rt.GET("/api/lesson/title/:ltitle",mint.GetLessonByTitle(db))
	//新建课
	rt.PUT("/api/lesson",mint.PutLesson(db)) 
	//修改
	rt.POST("/api/lesson",mint.PostLesson(db))//改
	//删除
	rt.DELETE("/api/lesson/:lid",mint.DeleteLesson(db)) 

	//文章
	//根据id查询
	rt.GET("/api/article/:aid",mint.GetArticle(db,rdb))
	//输入标题查询符合条件的 title% 
	rt.GET("/api/article/title/:title",mint.GetArticleByTitle(db))
	//新建课
	rt.PUT("/api/article",mint.PutArticle(db))
	//修改
	rt.POST("/api/article",mint.PostAritcle(db,rdb))//改
	//删除
	rt.DELETE("/api/article/:aid",mint.DeleteArticle(db,rdb))

	//文集
	//根据id查询
	rt.GET("/api/collection/:cid",mint.GetCollection(db))
	//输入标题查询符合条件的 title% 
	rt.GET("/api/collection/title/:title",mint.GetCollectionByTitle(db))
	//新建课
	rt.PUT("/api/collection",mint.PutCollection(db))
	//修改
	rt.POST("/api/collection",mint.PostCollection(db))//改
	//删除
	rt.DELETE("/api/collection/:cid",mint.DeleteCollection(db))

	//文章列表
	rt.GET("/api/article_list/collection/:cid",mint.GetCollectionArticleList(db))//改
	//修改
	rt.POST("/api/article_list/article/:aid",mint.PostArticleListByArticle(db))//改


	rt.DELETE("/api/article_list",mint.DeleteArticleInList(db,rdb))
	rt.DELETE("/api/article_list/article/:aid",mint.DeleteArticleInList(db,rdb))
	rt.DELETE("/api/article_list/collection/:cid",mint.DeleteCollectionInList(db,rdb))

	rt.Run()
}

