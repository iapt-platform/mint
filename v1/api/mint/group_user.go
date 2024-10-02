package mint

import (
	"net/http"
	"time"

	"github.com/gin-gonic/gin"
	"github.com/go-pg/pg/v10"
	"github.com/go-redis/redis/v8"
)

type GroupsUser struct {
	Id        int `form:"id" json:"id" `
	GroupId   int `form:"group_id" json:"group_id" `
	UserId    int `form:"user_id" json:"user_id" `
	CreatedAt time.Time
}

//display a list of all groups
func GroupsUsersIndex(db *pg.DB) gin.HandlerFunc {
	return func(c *gin.Context) {
		var dbData []GroupsUser
		view := c.Query("view")

		switch view {
		case "group":
			err := db.Model(&dbData).Column("uid", "name").Where("group_id = ?", c.Query("group")).Select()
			panicIf(err)
		case "user":
			err := db.Model(&dbData).Column("uid", "name").Where("user_id = ?", c.Query("user")).Select()
			panicIf(err)
		}
		// TODO 补充业务逻辑

		c.JSON(http.StatusOK, gin.H{
			"status": "success",
			"data":   dbData,
		})
	}
}

//create a new group-user
func GroupsUsersCreate(db *pg.DB) gin.HandlerFunc {
	return func(c *gin.Context) {

		var form GroupsUser

		if err := c.ShouldBindJSON(&form); err != nil {
			c.JSON(http.StatusBadRequest, gin.H{
				"status":  "fail",
				"message": err.Error(),
			})
			return
		}
		_, err := db.Model(form).Column("group_id", "user_id").Insert()
		if err != nil {
			panic(err)
		}

		//建立成功
		c.JSON(http.StatusOK, gin.H{
			"status": "success",
			"data":   form,
		})
	}
}

//delete a specific group-user
func GroupsUsersDestroy(db *pg.DB, rdb *redis.Client) gin.HandlerFunc {
	return func(c *gin.Context) {
		groupId := c.Query("group")
		userId := c.DefaultQuery("user", "")

		if userId == "" {
			//删除整个组
			_, err := db.Model((*GroupsUser)(nil)).Where("group_id = ?", groupId).Delete()
			panicIf(err)
		} else {
			//删除一个成员
			_, err := db.Model((*GroupsUser)(nil)).Where("group_id = ?", groupId).Where("user_id = ?", userId).Delete()
			panicIf(err)
		}

		c.JSON(http.StatusOK, gin.H{
			"status": "sucess",
			"data":   groupId,
		})

	}
}
