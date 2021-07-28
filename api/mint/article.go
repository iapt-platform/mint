package mint

import (
	"net/http"
	"strconv"
	"fmt"
	"github.com/gin-gonic/gin"
	"github.com/go-pg/pg/v10"
	"github.com/go-redis/redis/v8"
	"context"
	"time"
)
var ctx = context.Background()


/*
    id SERIAL PRIMARY KEY,
    uuid         VARCHAR (36) ,
    title        VARCHAR (32) NOT NULL,
    subtitle     VARCHAR (32),
    summary      VARCHAR (255),
    content      TEXT,
    owner_id     INTEGER  NOT NULL,
    owner        VARCHAR (36),
    setting      JSON,
    status       INTEGER   NOT NULL DEFAULT (10),
	version     INTEGER NOT NULL DEFAULT (1),
    deleted_at  TIMESTAMP,
    created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP

*/
type Article struct {
	Id     int `form:"id" json:"id" binding:"required"`
	Title string `form:"title" json:"title" binding:"required"`
	Subtitle string `form:"subtitle" json:"subtitle"`
	Summary string `form:"summary" json:"summary"`
	Content string `form:"content" json:"content"`
	OwnerId int
	Setting string `form:"setting" json:"setting"`
	Status int `form:"status" json:"status"`
	Version int
    DeletedAt time.Time
    CreatedAt time.Time
    UpdatedAt time.Time
}
//查询
func GetArticle(db *pg.DB, rdb *redis.Client) gin.HandlerFunc {
	return func(c *gin.Context) {
		lid,err := strconv.ParseInt(c.Param("aid"),10,64)
		if err != nil {
			panic(err)
		}
		fmt.Println("get article")
		rkey := "article://"+c.Param("aid")
		n, err := rdb.Exists(ctx,rkey).Result()
		if err != nil  {
			fmt.Println(err)
		}else if n == 0 {
			fmt.Println("redis key not exist")
		}else{
			fmt.Println("redis key exist")
			val, err := rdb.HGetAll(ctx, rkey).Result()
			if err != nil || val == nil {
				//有错误或者没查到
				fmt.Println("redis error")
					
			}else{
				fmt.Println("redis no error")
				c.JSON(http.StatusOK, gin.H{
					"data": val,
				})
				return
			}	
		}

		article := &Article{Id: int(lid)}
		err = db.Model(article).Column("id","title","subtitle","content","owner_id","setting","status","version","updated_at").WherePK().Select()
		if err != nil {
			panic(err)
		}			
		c.JSON(http.StatusOK, gin.H{
			"data": article,
		})
		//写入redis
		rdb.HSet(ctx,rkey,"id",article.Id)
		rdb.HSet(ctx,rkey,"title",article.Title)
		rdb.HSet(ctx,rkey,"subtitle",article.Subtitle)
		rdb.HSet(ctx,rkey,"content",article.Content)
		rdb.HSet(ctx,rkey,"owner_id",article.OwnerId)
		rdb.HSet(ctx,rkey,"setting",article.Setting)
		rdb.HSet(ctx,rkey,"status",article.Status)
		rdb.HSet(ctx,rkey,"version",article.Version)
		rdb.HSet(ctx,rkey,"updated_at",article.UpdatedAt)
			
	}
}

//查询
func GetArticleByTitle(db *pg.DB) gin.HandlerFunc {
	return func(c *gin.Context) {
		title:= c.Param("ltitle")

		// TODO 在这里进行db操作
		// Select user by primary key.
		var articles []Article
		err := db.Model(&articles).Column("id","title","subtitle").Where("title like ?",title+"%").Select()
		if err != nil {
			panic(err)
		}
		
		c.JSON(http.StatusOK, gin.H{
			"message": articles,
		})
	}
}

//新建-
//PUT http://127.0.0.1:8080/api/lesson?title=lesson-one&status=10
func PutArticle(db *pg.DB) gin.HandlerFunc{
	return func(c *gin.Context){
	
		title := c.Query("title")
		status1,err := strconv.ParseInt(c.Query("status"),10,64)
		if err != nil {
			panic(err)
		}

		newArticle := &Article{
			Title:   title,
			Status: int(status1),
			OwnerId:1,
		}
		_, err = db.Model(newArticle).Insert()
		if err != nil {
			panic(err)
		}

		//修改完毕
		c.JSON(http.StatusOK,gin.H{
			"message":"",
		})
	}
}


//修改
func PostAritcle(db *pg.DB,rdb *redis.Client) gin.HandlerFunc{
	return func(c *gin.Context){
		var form Article

		if err := c.ShouldBindJSON(&form); err != nil {
			c.JSON(http.StatusBadRequest, gin.H{"error": err.Error()})
			return
		}

		_,err := db.Model(&form).Column("title","subtitle","summary","status","content").WherePK().Update()
		if err != nil {
			panic(err)
		}
		c.JSON(http.StatusOK,gin.H{
			"message":"update ok",
		})
		rkey := "article://"+strconv.Itoa(form.Id)
		rdb.Del(ctx,rkey)
	}
}


//删
func DeleteArticle(db *pg.DB ,rdb *redis.Client) gin.HandlerFunc{
	return func(c *gin.Context){
		id,err := strconv.Atoi(c.Param("aid"))
		if err != nil {
			panic(err)
		}
		article := &Article{
			Id:int(id),
		}
		//删之前获取 course_id
		_, err = db.Model(article).WherePK().Delete()
		if err != nil {
			panic(err)
		}
		//TODO 删除article_list表相关项目
		c.JSON(http.StatusOK,gin.H{
			"message":"delete "+c.Param("lid"),
		})

		rkey := "article://"+c.Param("aid")
		rdb.Del(ctx,rkey)

	}
}