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
)

type Wbw struct {
	Id          int    `form:"id" json:"id" `
	PrParentId  int    `form:"pr_parent_id" json:"pr_parent_id" `
	WbwsIndexId int    `form:"wbws_index_id" json:"wbws_index_id"`
	ChannelId   int    `form:"channel_id" json:"channel_id"`
	BookId      int    `form:"book_id" json:"book_id"`
	Paragraph   int    `form:"paragraph" json:"paragraph"`
	Sn          int    `form:"sn" json:"sn"`
	Word        string `form:"word" json:"word"`
	Data        string `form:"data" json:"data"`
	Lang        string `form:"lang" json:"lang"`
	Status      string `form:"status" json:"status"`
	EditorId    int
	OwnerId     int
	Version     int
	DeletedAt   time.Time
	CreatedAt   time.Time
	UpdatedAt   time.Time
}
type WbwHolder struct {
	Data []Wbw
}

func (i *WbwHolder) UnmarshalJSON(b []byte) error {
	return json.Unmarshal(b, &i.Data)
}

//display a list of all wbws
func WbwsIndex(db *pg.DB) gin.HandlerFunc {
	return func(c *gin.Context) {
		active := c.Query("active")

		// TODO 补充业务逻辑
		var wbws []Wbw
		switch active {
		case "channel":
			//某channel
			channel := c.Query("channel")
			book := c.Query("book")
			paragraph := c.Query("paragraph")

			err := db.Model(&wbws).Column("id", "book_id", "paragraph", "sn", "data", "lang", "status").Where("channel = ?", channel).Where("book_id = ?", book).Where("paragraph = ?", paragraph).First()
			//errors.Is(err, gorm.ErrRecordNotFound)
			if err != nil {
				panic(err)
			}

		case "blockid":
			//某句子所有channel记录
			blockid := c.Query("blockid")
			err := db.Model(&wbws).Column("id", "book_id", "paragraph", "sn", "data", "lang", "status").Where("wbws_index_id = ?", blockid).Select()
			if err != nil {
				panic(err)
			}

		}

		c.JSON(http.StatusOK, gin.H{
			"status":  "succes",
			"message": "",
			"data":    wbws,
		})
	}
}

//return an HTML form for creating a new wbw
func WbwsNew(db *pg.DB) gin.HandlerFunc {
	return func(c *gin.Context) {
		//TODO 业务逻辑
		c.HTML(http.StatusOK, "wbws_new.html", gin.H{
			"message": "ok",
		})
	}
}

//create new wbw
func WbwsCreate(db *pg.DB) gin.HandlerFunc {
	return func(c *gin.Context) {
		/*
			cookie, err := c.Cookie("userid")
			if err != nil {
				fmt.Println("not login")
				c.JSON(http.StatusOK, gin.H{
					"status":  "fail",
					"message": "not login",
				})
				return
			}
			fmt.Println(cookie)
		*/
		iChannelId := c.Query("channel")
		//TODO 查询权限

		var form WbwHolder

		if err := c.ShouldBindJSON(&form); err != nil {
			c.JSON(http.StatusBadRequest, gin.H{"error": err.Error()})
			return
		}

		tx, err := db.Begin()
		if err != nil {
			panic(err)
		}
		defer tx.Rollback()
		stmt, err := tx.Prepare("INSERT INTO wbws ( channel_id, book_id,paragraph,sn,word,data,lang,status,editor_id,owner_id ) VALUES( $1, $2, $3, $4, $5, $6, $7, $8, $9, $10)")
		if err != nil {
			panic(err)
		}
		defer stmt.Close()
		for _, value := range form.Data {
			_, err = stmt.Exec(iChannelId, value.BookId, value.Paragraph, value.Sn, value.Word, value.Data, "en", "public", 1, 1)
			if err != nil {
				panic(err)
			}

		}
		err = tx.Commit()
		if err != nil {
			panic(err)
		}
		//建立成功
		c.JSON(http.StatusOK, gin.H{
			"status":  "succes",
			"message": "",
			"data":    form.Data,
		})
	}
}

//display a specific Wbw
func WbwsShow(db *pg.DB, rdb *redis.Client) gin.HandlerFunc {
	return func(c *gin.Context) {
		id, err := strconv.Atoi(c.Param("id"))
		if err != nil {
			panic(err)
		}
		fmt.Println("get wbw id=" + c.Param("id"))
		rkey := "wbw://id"
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

		wbw := &Wbw{Id: id}
		err = db.Model(wbw).Column("id", "uid", "name", "description", "description_type", "owner_id", "setting", "status", "version", "updated_at").WherePK().Select()
		if err != nil {
			panic(err)
		}
		c.JSON(http.StatusOK, gin.H{
			"data": wbw,
		})
		//写入redis
		rdb.HSet(ctx, rkey, id, wbw)

	}
}

//return an HTML form for edit a wbw
func WbwsEdit(db *pg.DB) gin.HandlerFunc {
	return func(c *gin.Context) {
		//TODO 业务逻辑
		c.HTML(http.StatusOK, "wbws_edit.html", gin.H{
			"name": "ok",
		})
	}
}

//update a specific wbw
func WbwsUpdate(db *pg.DB, rdb *redis.Client) gin.HandlerFunc {
	return func(c *gin.Context) {

		var form Wbw

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
		rkey := "wbw://id/" + strconv.Itoa(form.Id)
		rdb.Del(ctx, rkey)
	}
}

//delete a specific wbw
func WbwsDestroy(db *pg.DB, rdb *redis.Client) gin.HandlerFunc {
	return func(c *gin.Context) {
		id, err := strconv.Atoi(c.Param("id"))
		if err != nil {
			panic(err)
		}
		wbw := &Wbw{
			Id: int(id),
		}
		//删之前获取 course_id
		_, err = db.Model(wbw).WherePK().Delete()
		if err != nil {
			panic(err)
		}
		//删除article_list表相关项目
		c.JSON(http.StatusOK, gin.H{
			"message": c.Param("id"),
		})

		rkey := "wbw://id/" + c.Param("id")
		rdb.Del(ctx, rkey)

	}
}
