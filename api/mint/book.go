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

type Book struct {
	Id int `form:"id" json:"id" `

	Title     string `form:"title" json:"title"`
	Summary   string `form:"summary" json:"summary"`
	Lang      string `form:"lang" json:"lang"`
	ChannelId int    `form:"channel_id" json:"channel_id"`

	Status string `form:"status" json:"status"`

	OwnerId   int
	Version   int
	DeletedAt time.Time

	CreatedAt time.Time
	UpdatedAt time.Time
}

//display a list of all books
func BooksIndex(db *pg.DB) gin.HandlerFunc {
	return func(c *gin.Context) {
		title := c.DefaultQuery("title", "")

		// TODO 补充业务逻辑
		var books []Book
		err := db.Model(&books).Column("id", "title").Where("name like ?", title+"%").Select()
		if err != nil {
			panic(err)
		}

		c.JSON(http.StatusOK, gin.H{
			"data": books,
		})
	}
}

//return an HTML form for creating a new book
func BooksNew(db *pg.DB) gin.HandlerFunc {
	return func(c *gin.Context) {
		//TODO 业务逻辑
		c.HTML(http.StatusOK, "books/new.html", gin.H{
			"message": "ok",
		})
	}
}

//create a new book
func BooksCreate(db *pg.DB) gin.HandlerFunc {
	return func(c *gin.Context) {

		var form Book

		if err := c.ShouldBindJSON(&form); err != nil {
			c.JSON(http.StatusBadRequest, gin.H{"error": err.Error()})
			return
		}
		form.OwnerId = 1
		_, err := db.Model(&form).Insert()
		if err != nil {
			panic(err)
		}

		//建立成功
		c.JSON(http.StatusOK, gin.H{
			"status": "sucess",
			"data":   form,
		})
	}
}

//display a specific Book
func BooksShow(db *pg.DB, rdb *redis.Client) gin.HandlerFunc {
	return func(c *gin.Context) {
		id, err := strconv.Atoi(c.Param("id"))
		if err != nil {
			panic(err)
		}
		fmt.Println("get book id=" + c.Param("id"))
		rkey := "book://id"
		n, err := rdb.Exists(ctx, rkey).Result()
		if err != nil {
			fmt.Println(err)
		} else if n == 0 {
			fmt.Println("redis key not exist")
		} else {
			fmt.Println("redis key exist")
			val, err := rdb.HGetAll(ctx, rkey).Result()
			if err != nil || val == nil {
				//有错误或者没查到
				fmt.Println("redis error")

			} else {
				fmt.Println("redis no error")
				c.JSON(http.StatusOK, gin.H{
					"data": val,
				})
				return
			}
		}

		book := &Book{Id: id}
		err = db.Model(book).Column("id", "uid", "name", "description", "description_type", "owner_id", "setting", "status", "version", "updated_at").WherePK().Select()
		if err != nil {
			panic(err)
		}
		c.JSON(http.StatusOK, gin.H{
			"data": book,
		})
		//写入redis
		rdb.HSet(ctx, rkey, id, book)

	}
}

//return an HTML form for edit a book
func BooksEdit(db *pg.DB) gin.HandlerFunc {
	return func(c *gin.Context) {
		//TODO 业务逻辑
		c.HTML(http.StatusOK, "books/edit.html", gin.H{
			"name": "ok",
		})
	}
}

//update a specific book
func BooksUpdate(db *pg.DB, rdb *redis.Client) gin.HandlerFunc {
	return func(c *gin.Context) {
		var form Book

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
		rkey := "book://id/" + strconv.Itoa(form.Id)
		rdb.Del(ctx, rkey)
	}
}

//delete a specific book
func BooksDestroy(db *pg.DB, rdb *redis.Client) gin.HandlerFunc {
	return func(c *gin.Context) {
		id, err := strconv.Atoi(c.Param("id"))
		if err != nil {
			panic(err)
		}
		book := &Book{
			Id: int(id),
		}

		_, err = db.Model(book).WherePK().Delete()
		if err != nil {
			panic(err)
		}

		rkey := "book://id/" + c.Param("id")
		rdb.Del(ctx, rkey)

		c.JSON(http.StatusOK, gin.H{
			"message": c.Param("id"),
		})

	}
}
