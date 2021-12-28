package mint

import (
	"net/http"
	"strconv"
	"fmt"
	"github.com/gin-gonic/gin"
	"github.com/go-pg/pg/v10"
	"github.com/go-redis/redis/v8"
	"time"
)


type RefDict struct {
	Id     int `form:"id" json:"id" `
	Word string `form:"word" json:"word" `
	WordEn string `form:"type" json:"type"`

	Meaning string `form:"meaning" json:"meaning"`
	Lang string `form:"lang" json:"lang"`

	RefDictNameId int
    CreatedAt time.Time
}


//display a list of all dictionaries
func RefDictsIndex(db *pg.DB) gin.HandlerFunc {
	return func(c *gin.Context) {
		word:= c.DefaultQuery("word","")

		// TODO 补充业务逻辑
		var dictionaries []RefDict
		err := db.Model(&dictionaries).Column("id","word","type","grammar","meaning").Where("word = ? ",word).Select()
		if err != nil {
			panic(err)
		}
		
		c.JSON(http.StatusOK, gin.H{
			"message":"success",
			"data": dictionaries,
		})
	}
}


//display a specific RefDict
func RefDictsShow(db *pg.DB, rdb *redis.Client) gin.HandlerFunc {
	return func(c *gin.Context) {
		id,err := strconv.Atoi(c.Param("id"))
		if err != nil {
			panic(err)
		}
		fmt.Println("get refdict id=" + c.Param("id"))
		rkey := "refdict://id/"+c.Param("id")
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

		refdict := &RefDict{Id: id}
		err = db.Model(refdict).Column("id","uid","name","description","description_type","owner_id","setting","status","version","updated_at").WherePK().Select()
		if err != nil {
			panic(err)
		}			

		//写入redis
		rdb.HSet(ctx,rkey,"id",refdict.Id)
		rdb.HSet(ctx,rkey,"word",refdict.Word)
		rdb.HSet(ctx,rkey,"word_en",refdict.WordEn)
		rdb.HSet(ctx,rkey,"meaning",refdict.Meaning)
		rdb.HSet(ctx,rkey,"lang",refdict.Lang)
		rdb.HSet(ctx,rkey,"ref_dict_name_id",refdict.RefDictNameId)

		c.JSON(http.StatusOK, gin.H{
			"message":"success",
			"data": refdict,
		})		
			
	}
}

