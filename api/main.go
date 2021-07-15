package main

import (
	"github.com/gin-gonic/gin"
	"github.com/go-pg/pg/v10"
	"github.com/iapt-platform/mint"
	"fmt"
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
	rt.POST("/api/course/lessonnum/:cid/:num",mint.PostLessonNumInCousrse(db))

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
	rt.GET("/api/article/:aid",mint.GetArticle(db))
	//输入标题查询符合条件的 title% 
	rt.GET("/api/article/title/:title",mint.GetArticleByTitle(db))
	//新建课
	rt.PUT("/api/article",mint.PutArticle(db))
	//修改
	rt.POST("/api/article",mint.PostAritcle(db))//改
	//删除
	rt.DELETE("/api/article/:aid",mint.DeleteArticle(db))

	rt.Run()
}
