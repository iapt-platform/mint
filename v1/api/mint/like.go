package mint

import (
	"net/http"
	"strconv"
	"time"

	"github.com/gin-gonic/gin"
	"github.com/go-pg/pg/v10"
)

type Like struct {
	Id int `form:"id" json:"id" binding:"required"`

	LikeType string `form:"like_type" json:"like_type"`

	ResourceId   string `form:"resource_id" json:"resource_id"`
	ResourceType string `form:"resource_type" json:"resource_type"`

	UserId int    `form:"user_id" json:"user_id"`
	Emoji  string `form:"emoji" json:"emoji"`

	CreatedAt time.Time
}

//查询display a list of all Likes
func LikesIndex(db *pg.DB) gin.HandlerFunc {
	return func(c *gin.Context) {
		//按照标题搜索
		resource_id := c.Query("resource_id")
		resource_type := c.Query("resource_type")

		// TODO 在这里进行db操作
		// Select user by primary key.
		var likes []Like

		err := db.Model(&likes).Column("id", "resource_id", "resource_type", "like_type", "user_id", "emoji").Where("resource_id = ?", resource_id).Where("resource_type = ?", resource_type).Select()
		if err != nil {
			panic(err)
		}

		c.JSON(http.StatusOK, gin.H{
			"message": likes,
		})
	}
}

//新建create a new Likes
func LikesCreate(db *pg.DB) gin.HandlerFunc {
	return func(c *gin.Context) {

		var form Channel

		if err := c.ShouldBindJSON(&form); err != nil {
			c.JSON(http.StatusBadRequest, gin.H{"error": err.Error()})
			return
		}
		_, err := db.Model(&form).Insert()
		if err != nil {
			panic(err)
		}

		c.JSON(http.StatusOK, gin.H{
			"status": "sucess",
			"data":   form,
		})
	}
}

//删
//delete a specific Likes
func LikesDestroy(db *pg.DB) gin.HandlerFunc {
	return func(c *gin.Context) {
		id, err := strconv.Atoi(c.Param("id"))
		if err != nil {
			panic(err)
		}
		like := &Like{
			Id: id,
		}

		_, err = db.Model(like).WherePK().Delete()
		if err != nil {
			panic(err)
		}

		c.JSON(http.StatusOK, gin.H{
			"message": c.Param("id"),
		})
	}
}
