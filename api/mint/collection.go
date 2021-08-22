package mint

import (
	"fmt"
	"github.com/gin-gonic/gin"
	"github.com/go-pg/pg/v10"
	"net/http"
	"strconv"
	"time"
)

/*
    id SERIAL PRIMARY KEY,
    uuid         VARCHAR (36) ,
    title        VARCHAR (32) NOT NULL,
    subtitle     VARCHAR (32),
    summary      VARCHAR (255),
    article_list TEXT,
    status       INTEGER   NOT NULL DEFAULT (10),
    creator_id   INTEGER,
    owner        VARCHAR (36),
    lang         CHAR (8),
	version     INTEGER NOT NULL DEFAULT (1),
    deleted_at  TIMESTAMP,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP

*/
type Collection struct {
	Id          int    `form:"id" json:"id" binding:"required"`
	Title       string `form:"title" json:"title" binding:"required"`
	Subtitle    string `form:"subtitle" json:"subtitle" binding:"required"`
	Summary     string `form:"summary" json:"summary" binding:"required"`
	ArticleList string `form:"article_list" json:"article_list" binding:"required"`
	CreatorId   int
	Lang        string `form:"lang" json:"lang" binding:"required"`
	Status      int    `form:"status" json:"status" binding:"required"`
	Version     int
	DeletedAt   time.Time
	CreatedAt   time.Time
	UpdatedAt   time.Time
}

//查询
func GetCollection(db *pg.DB) gin.HandlerFunc {
	return func(c *gin.Context) {
		lid, err := strconv.Atoi(c.Param("cid"))
		if err != nil {
			panic(err)
		}
		fmt.Println("get lesson")
		// TODO 在这里进行db操作
		// Select user by primary key.
		collection := &Collection{Id: int(lid)}
		err = db.Model(collection).Column("title", "subtitle", "summary", "status").WherePK().Select()
		if err != nil {
			panic(err)
		}

		c.JSON(http.StatusOK, gin.H{
			"message": collection,
		})
	}
}

//查询
func GetCollectionByTitle(db *pg.DB) gin.HandlerFunc {
	return func(c *gin.Context) {
		title := c.Param("ctitle")

		// TODO 在这里进行db操作
		// Select user by primary key.
		var collections []Collection
		err := db.Model(&collections).Column("id", "title", "subtitle").Where("title like ?", title+"%").Select()
		if err != nil {
			panic(err)
		}

		c.JSON(http.StatusOK, gin.H{
			"message": collections,
		})
	}
}

//新建-
//PUT http://127.0.0.1:8080/api/lesson?title=lesson-one&course_id=1&status=10
func PutCollection(db *pg.DB) gin.HandlerFunc {
	return func(c *gin.Context) {

		title := c.Query("title")
		status1, err := strconv.Atoi(c.Query("status"))
		if err != nil {
			panic(err)
		}

		newOne := &Collection{
			Title:     title,
			Status:    int(status1),
			CreatorId: 1,
		}
		_, err = db.Model(newOne).Insert()
		if err != nil {
			panic(err)
		}

		//修改完毕
		c.JSON(http.StatusOK, gin.H{
			"message": "insert ok",
		})
	}
}

//改
func PostCollection(db *pg.DB) gin.HandlerFunc {
	return func(c *gin.Context) {
		var form Collection

		if err := c.ShouldBindJSON(&form); err != nil {
			c.JSON(http.StatusBadRequest, gin.H{"error": err.Error()})
			return
		}

		_, err := db.Model(&form).Column("title", "subtitle", "summary", "status", "article_list").WherePK().Update()
		if err != nil {
			panic(err)
		}
		c.JSON(http.StatusOK, gin.H{
			"message": "update ok",
		})
	}
}

//删
func DeleteCollection(db *pg.DB) gin.HandlerFunc {
	return func(c *gin.Context) {
		id, err := strconv.Atoi(c.Param("cid"))
		if err != nil {
			panic(err)
		}
		collection := &Collection{
			Id: int(id),
		}
		//删之前获取 course_id
		_, err = db.Model(collection).WherePK().Delete()
		if err != nil {
			panic(err)
		}

		c.JSON(http.StatusOK, gin.H{
			"message": "deleted " + c.Param("cid"),
		})
	}
}
