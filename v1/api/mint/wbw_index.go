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

type WbwIndex struct {
	Id        int `form:"id" json:"id" `
	ChannelId int `form:"channel_id" json:"channel_id"`
	BookId    int `form:"book_id" json:"book_id"`
	Paragraph int `form:"paragraph" json:"paragraph"`
	OwnerId   int
	CreatedAt time.Time
}
type WbwIndexHolder struct {
	Data []WbwIndex
}

func (i *WbwIndexHolder) UnmarshalJSON(b []byte) error {
	return json.Unmarshal(b, &i.Data)
}

//display a list of all wbws
func WbwIndexsIndex(db *pg.DB) gin.HandlerFunc {
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

//create new wbw
func WbwIndexsCreate(db *pg.DB) gin.HandlerFunc {
	return func(c *gin.Context) {
		//channel := c.Query("channel")

		var form WbwHolder

		if err := c.ShouldBindJSON(&form); err != nil {
			c.JSON(http.StatusBadRequest, gin.H{"error": err.Error()})
			return
		}
		//TODO 查询权限
		tx, err := db.Begin()
		if err != nil {
			panic(err)
		}
		defer tx.Rollback()
		stmt, err := tx.Prepare("INSERT INTO wbw( channel_id, book_id,paragraph,sn,word,data,lang,status,editor_id,owner_id ) VALUES( $1, $2, $3, $4, $5, $6, $7, $8, $9, $10)")
		if err != nil {
			panic(err)
		}
		defer stmt.Close()
		for _, value := range form.Data {
			_, err = stmt.Exec(value.ChannelId, value.BookId, value.Paragraph, value.Sn, value.Word, value.Data, value.Lang, value.Status, 1, 1)
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
func WbwIndexsShow(db *pg.DB, rdb *redis.Client) gin.HandlerFunc {
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

//delete a specific wbw
func WbwIndexsDestroy(db *pg.DB, rdb *redis.Client) gin.HandlerFunc {
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
