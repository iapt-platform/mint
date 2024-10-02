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

type Group struct {
	Id              int    `form:"id" json:"id" `
	Uid             string `form:"uid" json:"uid" `
	Name            string `form:"name" json:"name"`
	Description     string `form:"description" json:"description"`
	DescriptionType string `form:"description_type" json:"description_type"`
	Setting         string `form:"setting" json:"setting"`
	Status          string `form:"status" json:"status"`
	OwnerId         int
	Version         int
	DeletedAt       time.Time
	CreatedAt       time.Time
	UpdatedAt       time.Time
}

//display a list of all groups
func GroupsIndex(db *pg.DB) gin.HandlerFunc {
	return func(c *gin.Context) {
		view := c.Query("view")
		switch view {
		case "owner":
		case "write":
		case "read":
		}
		// TODO 补充业务逻辑
		var groups []Group
		err := db.Model(&groups).Column("id", "uid", "name").Where("owner_id = ?", 1).Select()
		if err != nil {
			panic(err)
		}

		c.JSON(http.StatusOK, gin.H{
			"status": "success",
			"data":   groups,
		})
	}
}

//return an HTML form for creating a new group
func GroupsNew(db *pg.DB) gin.HandlerFunc {
	return func(c *gin.Context) {
		//TODO 业务逻辑
		c.HTML(http.StatusOK, "group_new.html", gin.H{
			"message": "ok",
		})
	}
}

//create a new group
func GroupsCreate(db *pg.DB) gin.HandlerFunc {
	return func(c *gin.Context) {

		name := c.Query("name")
		status := c.DefaultQuery("status", "private")

		newGroup := &Group{
			Uid:     uuid.New().String(),
			Name:    name,
			Status:  status,
			OwnerId: 1, //TODO user_id
		}
		_, err := db.Model(newGroup).Column("name", "status").Insert()
		if err != nil {
			panic(err)
		}

		//建立成功
		c.JSON(http.StatusOK, gin.H{
			"status": "success",
			"data":   newGroup,
		})
	}
}

//display a specific Group
func GroupsShow(db *pg.DB, rdb *redis.Client) gin.HandlerFunc {
	return func(c *gin.Context) {
		id, err := strconv.Atoi(c.Param("id"))
		if err != nil {
			panic(err)
		}

		rkey := "group://id"
		n, err := rdb.HExists(ctx, rkey, c.Param("id")).Result()
		if err == nil && n {
			val, err := rdb.HGet(ctx, rkey, c.Param("id")).Result()
			if err == nil {
				var redisData Group
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

		group := &Group{Id: id}
		err = db.Model(group).Column("id", "uid", "name", "description", "description_type", "owner_id", "setting", "status", "version", "updated_at").WherePK().First()
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
				"data":   group,
			})
			//写入redis
			jsonData, err := json.Marshal(group)
			if err == nil {
				rdb.HSet(ctx, rkey, c.Param("id"), string(jsonData))
			}
		}

	}
}

//return an HTML form for edit a group
func GroupsEdit(db *pg.DB) gin.HandlerFunc {
	return func(c *gin.Context) {
		//TODO 业务逻辑
		c.HTML(http.StatusOK, "groups_edit.html", gin.H{
			"name": "ok",
		})
	}
}

//update a specific group
func GroupsUpdate(db *pg.DB, rdb *redis.Client) gin.HandlerFunc {
	return func(c *gin.Context) {

		var form Group

		if err := c.ShouldBindJSON(&form); err != nil {
			c.JSON(http.StatusBadRequest, gin.H{
				"status":  "fail",
				"message": err.Error(),
			})
			return
		}

		//补充业务逻辑
		_, err := db.Model(&form).Column("name", "description", "description_type", "status", "setting").WherePK().Update()
		if err != nil {
			panic(err)
		}
		c.JSON(http.StatusOK, gin.H{
			"status": "sucess",
			"data":   form,
		})
		//delete redis
		rkey := "group://id"
		rdb.HDel(ctx, rkey, c.Param("id"))
	}
}

//delete a specific group
func GroupsDestroy(db *pg.DB, rdb *redis.Client) gin.HandlerFunc {
	return func(c *gin.Context) {
		id, err := strconv.Atoi(c.Param("id"))
		if err != nil {
			panic(err)
		}
		group := &Group{
			Id: id,
		}

		_, err = db.Model(group).WherePK().Delete()
		if err != nil {
			panic(err)
		}

		c.JSON(http.StatusOK, gin.H{
			"status": "sucess",
			"data":   c.Param("id"),
		})

		rkey := "group://id"
		rdb.Del(ctx, rkey, c.Param("id"))

	}
}
