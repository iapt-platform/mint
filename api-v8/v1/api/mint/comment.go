package mint

import (
	"encoding/json"
	"fmt"
	"net/http"
	"strconv"
	"time"

	"github.com/gin-gonic/gin"
	"github.com/go-pg/pg/v10"
	"github.com/go-redis/redis/v8"
	"github.com/google/uuid"
)

type Comment struct {
	Id          int    `form:"id" json:"id" `
	Uid         string `form:"uid" json:"uid" `
	ParentId    int    `form:"parent_id" json:"parent_id"`
	ParentType  string `form:"parent_type" json:"parent_type"`
	Title       string `form:"title" json:"title"`
	Content     string `form:"content" json:"content"`
	ContentType string `form:"content_type" json:"content_type"`
	Status      string `form:"status" json:"status"`
	OwnerId     int
	Version     int
	CreatedAt   time.Time
	UpdatedAt   time.Time
}

//display a list of all comments
func CommentsIndex(db *pg.DB) gin.HandlerFunc {
	return func(c *gin.Context) {
		parentid := c.Query("parentid")
		parenttype := c.Query("parenttype")

		// TODO 补充业务逻辑
		var comments []Comment
		err := db.Model(&comments).Column("id", "title", "content", "content_type", "status").Where("parent_id = ?", parentid).Where("parent_type = ?", parenttype).Where("status = checked").Select()
		if err != nil {
			panic(err)
		}

		c.JSON(http.StatusOK, gin.H{
			"status": "success",
			"data":   comments,
		})
	}
}

//return an HTML form for creating a new comment
func CommentsNew(db *pg.DB) gin.HandlerFunc {
	return func(c *gin.Context) {
		//TODO 业务逻辑
		c.HTML(http.StatusOK, "comments_new.html", gin.H{
			"message": "ok",
		})
	}
}

//create a new comment
func CommentsCreate(db *pg.DB) gin.HandlerFunc {
	return func(c *gin.Context) {

		var newComment Comment

		if err := c.ShouldBindJSON(&newComment); err != nil {
			c.JSON(http.StatusBadRequest, gin.H{
				"status":  "fail",
				"message": err.Error(),
			})
			return
		}
		newComment.Uid = uuid.New().String()
		newComment.Status = "checking"
		newComment.OwnerId = 1
		_, err := db.Model(&newComment).Column("id", "uid", "parent_id", "parent_type", "title", "content", "content_type", "status", "owner_id").Insert()
		if err != nil {
			panic(err)
		}

		//建立成功
		c.JSON(http.StatusOK, gin.H{
			"status": "success",
			"data":   newComment,
		})
	}
}

//display a specific Comment
func CommentsShow(db *pg.DB, rdb *redis.Client) gin.HandlerFunc {
	return func(c *gin.Context) {
		id, err := strconv.Atoi(c.Param("id"))
		if err != nil {
			panic(err)
		}
		rkey := "comment://id"
		n, err := rdb.HExists(ctx, rkey, c.Param("id")).Result()
		if err == nil && n {
			val, err := rdb.HGet(ctx, rkey, c.Param("id")).Result()
			if err == nil {
				var redisData Comment
				json.Unmarshal([]byte(val), &redisData)
				c.JSON(http.StatusOK, gin.H{
					"status": "success",
					"data":   redisData,
				})
				return
			} else {
				fmt.Println(err)
			}
		} else {
			fmt.Println(err)
		}

		comment := &Comment{Id: id}
		err = db.Model(comment).Column("id", "uid", "parent_id", "parent_type", "title", "content", "content_type", "status", "owner_id").WherePK().First()
		if err != nil {
			if err.Error() == pg.ErrNoRows.Error() {
				c.JSON(http.StatusOK, gin.H{
					"status":  "fail",
					"message": "no-rows",
				})
			} else {
				panic(err)
			}
		} else {
			c.JSON(http.StatusOK, gin.H{
				"status": "sucess",
				"data":   comment,
			})
			//写入redis
			jsonData, err := json.Marshal(comment)
			if err == nil {
				rdb.HSet(ctx, rkey, c.Param("id"), string(jsonData))
			}
		}

	}
}

//return an HTML form for edit a comment
func CommentsEdit(db *pg.DB) gin.HandlerFunc {
	return func(c *gin.Context) {
		//TODO 业务逻辑
		c.HTML(http.StatusOK, "comment_edit.html", gin.H{
			"name": "ok",
		})
	}
}

//update a specific comment
func CommentsUpdate(db *pg.DB, rdb *redis.Client) gin.HandlerFunc {
	return func(c *gin.Context) {
		var form Comment

		if err := c.ShouldBindJSON(&form); err != nil {
			c.JSON(http.StatusBadRequest, gin.H{
				"status":  "fail",
				"message": err.Error(),
			})
			return
		}

		//补充业务逻辑
		_, err := db.Model(&form).Column("title", "content", "content_type", "status").WherePK().Update()
		if err != nil {
			panic(err)
		}
		c.JSON(http.StatusOK, gin.H{
			"status": "sucess",
			"data":   form,
		})
		//delete redis
		rkey := "comment://id"
		rdb.HDel(ctx, rkey, c.Param("id"))
	}
}

//delete a specific comment
func CommentsDestroy(db *pg.DB, rdb *redis.Client) gin.HandlerFunc {
	return func(c *gin.Context) {
		id, err := strconv.Atoi(c.Param("id"))
		if err != nil {
			panic(err)
		}
		comment := &Comment{
			Id: int(id),
		}

		_, err = db.Model(comment).WherePK().Delete()
		if err != nil {
			panic(err)
		}

		rkey := "comment://id"
		rdb.HDel(ctx, rkey, c.Param("id"))

		c.JSON(http.StatusOK, gin.H{
			"status": "sucess",
			"data":   c.Param("id"),
		})

	}
}
