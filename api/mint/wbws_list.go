package mint

import (
	"fmt"
	"net/http"
	"strconv"
	"time"

	"github.com/gin-gonic/gin"
	"github.com/go-pg/pg/v10"
	"github.com/go-redis/redis/v8"
)

type WbwsList struct {
	Id        int    `form:"id" json:"id" `
	ParentId  int    `form:"parent_id" json:"parent_id" `
	ChannelId int    `form:"channel_id" json:"channel_id" `
	BookId    int    `form:"book_id" json:"book_id" `
	Paragraph int    `form:"paragraph" json:"paragraph" `
	Title     string `form:"title" json:"title"`
	Content   string `form:"content" json:"content"`
	Lang      string `form:"lang" json:"lang"`
	Setting   string `form:"setting" json:"setting"`
	Status    string `form:"status" json:"status"`
	OwnerId   int
	Version   int
	DeletedAt time.Time
	CreatedAt time.Time
	UpdatedAt time.Time
}

//display a list of all wbwsLists
func WbwsListsIndex(db *pg.DB) gin.HandlerFunc {
	return func(c *gin.Context) {
		name := c.DefaultQuery("name", "")

		// TODO 补充业务逻辑
		var wbwsLists []WbwsList
		err := db.Model(&wbwsLists).Column("id", "name").Where("name like ?", name+"%").Select()
		if err != nil {
			panic(err)
		}

		c.JSON(http.StatusOK, gin.H{
			"data": wbwsLists,
		})
	}
}

//return an HTML form for creating a new wbwsList
func WbwsListsNew(db *pg.DB) gin.HandlerFunc {
	return func(c *gin.Context) {
		//TODO 业务逻辑
		c.HTML(http.StatusOK, "wbwsLists/new.html", gin.H{
			"message": "ok",
		})
	}
}

//create a new wbwsList
func WbwsListsCreate(db *pg.DB) gin.HandlerFunc {
	return func(c *gin.Context) {

		name := c.Query("title")
		status := c.DefaultQuery("status", "private")

		newWbwsList := &WbwsList{
			Title:   name,
			Status:  status,
			OwnerId: 1, //TODO user_id
		}
		_, err := db.Model(newWbwsList).Insert()
		if err != nil {
			panic(err)
		}

		//建立成功
		c.JSON(http.StatusOK, gin.H{
			"message": "ok",
		})
	}
}

//display a specific WbwsList
func WbwsListsShow(db *pg.DB, rdb *redis.Client) gin.HandlerFunc {
	return func(c *gin.Context) {
		id, err := strconv.Atoi(c.Param("id"))
		if err != nil {
			panic(err)
		}
		fmt.Println("get wbwsList id=" + c.Param("id"))
		rkey := "wbwslist://id"
		n, err := rdb.HExists(ctx, rkey, c.Param("id")).Result()
		if err == nil && n {
			val, err := rdb.HGet(ctx, rkey, c.Param("id")).Result()
			if err == nil {
				c.JSON(http.StatusOK, gin.H{
					"data": val,
				})
				return
			} else {
				//有错误或者没查到
				fmt.Println("redis error")
			}
		} else {
			fmt.Println("redis error or key not exist")
		}

		wbwsList := &WbwsList{Id: id}
		err = db.Model(wbwsList).Column("id", "parent_id", "channel_id", "book_id", "paragraph", "title", "status", "setting", "content", "version", "updated_at").WherePK().Select()
		if err != nil {
			panic(err)
		}
		c.JSON(http.StatusOK, gin.H{
			"data": wbwsList,
		})
		//写入redis
		rdb.HSet(ctx, rkey, id, wbwsList)

	}
}

//return an HTML form for edit a wbwsList
func WbwsListsEdit(db *pg.DB) gin.HandlerFunc {
	return func(c *gin.Context) {
		//TODO 业务逻辑
		c.HTML(http.StatusOK, "wbwsLists/edit.html", gin.H{
			"name": "ok",
		})
	}
}

//update a specific wbwsList
func WbwsListsUpdate(db *pg.DB, rdb *redis.Client) gin.HandlerFunc {
	return func(c *gin.Context) {
		var form WbwsList

		if err := c.ShouldBindJSON(&form); err != nil {
			c.JSON(http.StatusBadRequest, gin.H{"error": err.Error()})
			return
		}

		//补充业务逻辑
		_, err := db.Model(&form).Column("name", "description", "description_type", "status", "setting").WherePK().Update()
		if err != nil {
			panic(err)
		}
		c.JSON(http.StatusOK, gin.H{
			"message": form,
		})
		//delete redis
		rkey := "wbwslist://id"
		rdb.HDel(ctx, rkey, strconv.Itoa(form.Id))
	}
}

//delete a specific wbwsList
func WbwsListsDestroy(db *pg.DB, rdb *redis.Client) gin.HandlerFunc {
	return func(c *gin.Context) {
		id, err := strconv.Atoi(c.Param("id"))
		if err != nil {
			panic(err)
		}
		wbwsList := &WbwsList{
			Id: int(id),
		}

		_, err = db.Model(wbwsList).WherePK().Delete()
		if err != nil {
			panic(err)
		}

		rkey := "wbwslist://id"
		rdb.HDel(ctx, rkey, c.Param("id"))

		c.JSON(http.StatusOK, gin.H{
			"message": c.Param("id"),
		})

	}
}
