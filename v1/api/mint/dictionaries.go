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


type Dictionary struct {
	Id     int `form:"id" json:"id" `
	Word string `form:"word" json:"word" `
	Type string `form:"type" json:"type"`
	Grammar string `form:"grammar" json:"grammar"`
	Base string `form:"base" json:"base"`

	Meaning string `form:"meaning" json:"meaning"`
	Note string `form:"note" json:"note"`
	Factors string `form:"factors" json:"factors"`
	FactorsMeaning string `form:"factors_meaning" json:"factors_meaning"`
	Lang string `form:"lang" json:"lang"`
	Sourse string `form:"sourse" json:"sourse"`

	OwnerId int
	Version int
    DeletedAt time.Time
    CreatedAt time.Time
    UpdatedAt time.Time
}


//display a list of all dictionaries
func DictionariesIndex(db *pg.DB) gin.HandlerFunc {
	return func(c *gin.Context) {
		word:= c.DefaultQuery("word","")

		// TODO 补充业务逻辑
		var dictionaries []Dictionary
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

//return an HTML form for creating a new dictionary
func DictionariesNew(db *pg.DB) gin.HandlerFunc{
	return func(c *gin.Context){
		//TODO 业务逻辑
		c.HTML(http.StatusOK,"dictionaries/new.html",gin.H{
			"message":"ok",
		})
	}
}
//create a new dictionary
func DictionariesCreate(db *pg.DB) gin.HandlerFunc{
	return func(c *gin.Context){
	
		var form Dictionary

		if err := c.ShouldBindJSON(&form); err != nil {
			c.JSON(http.StatusBadRequest, gin.H{"error": err.Error()})
			return
		}
		form.OwnerId = 1
		//TODO补充业务逻辑
		_, err := db.Model(&form).Insert()
		if err != nil {
			panic(err)
		}

		//建立成功
		c.JSON(http.StatusOK,gin.H{
			"message":"success",
			"data":form,
		})
	}
}

//display a specific Dictionary
func DictionariesShow(db *pg.DB, rdb *redis.Client) gin.HandlerFunc {
	return func(c *gin.Context) {
		id,err := strconv.Atoi(c.Param("id"))
		if err != nil {
			panic(err)
		}
		fmt.Println("get dictionary id=" + c.Param("id"))
		rkey := "dictionary://id/"+c.Param("id")
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

		dictionary := &Dictionary{Id: id}
		err = db.Model(dictionary).Column("id","uid","name","description","description_type","owner_id","setting","status","version","updated_at").WherePK().Select()
		if err != nil {
			panic(err)
		}			

		//写入redis
		rdb.HSet(ctx,rkey,"id",dictionary.Id)
		rdb.HSet(ctx,rkey,"word",dictionary.Word)
		rdb.HSet(ctx,rkey,"type",dictionary.Type)
		rdb.HSet(ctx,rkey,"grammar",dictionary.Grammar)
		rdb.HSet(ctx,rkey,"base",dictionary.Base)
		rdb.HSet(ctx,rkey,"meaning",dictionary.Meaning)
		rdb.HSet(ctx,rkey,"note",dictionary.Note)
		rdb.HSet(ctx,rkey,"factors",dictionary.Factors)
		rdb.HSet(ctx,rkey,"factors_meaning",dictionary.FactorsMeaning)
		rdb.HSet(ctx,rkey,"lang",dictionary.Lang)
		rdb.HSet(ctx,rkey,"sourse",dictionary.Sourse)
		rdb.HSet(ctx,rkey,"updated_at",dictionary.UpdatedAt)

		c.JSON(http.StatusOK, gin.H{
			"message":"success",
			"data": dictionary,
		})		
			
	}
}

//return an HTML form for edit a dictionary
func DictionariesEdit(db *pg.DB) gin.HandlerFunc{
	return func(c *gin.Context){
		//TODO 业务逻辑
		c.HTML(http.StatusOK,"dictionaries/edit.html",gin.H{
			"name":"ok",
		})
	}
}

//update a specific dictionary
func DictionariesUpdate(db *pg.DB,rdb *redis.Client) gin.HandlerFunc{
	return func(c *gin.Context){
		var form Dictionary

		if err := c.ShouldBindJSON(&form); err != nil {
			c.JSON(http.StatusBadRequest, gin.H{"error": err.Error()})
			return
		}

		//补充业务逻辑
		_,err := db.Model(&form).Column("type","grammar","base","meaning","note","factors","factors_meaning","lang","sourse").WherePK().Update()
		if err != nil {
			panic(err)
		}
		//delete redis
		rkey := "dictionary://id/"+strconv.Itoa(form.Id)
		rdb.Del(ctx,rkey)	

		c.JSON(http.StatusOK,gin.H{
			"message":"success",
			"data":form,
		})

	}
}


//delete a specific dictionary
func DictionariesDestroy(db *pg.DB ,rdb *redis.Client) gin.HandlerFunc{
	return func(c *gin.Context){
		id,err := strconv.Atoi(c.Param("id"))
		if err != nil {
			panic(err)
		}
		dictionary := &Dictionary{
			Id:int(id),
		}

		_, err = db.Model(dictionary).WherePK().Delete()
		if err != nil {
			panic(err)
		}

		rkey := "dictionary://id/"+c.Param("id")
		rdb.Del(ctx,rkey)
		
		c.JSON(http.StatusOK,gin.H{
			"message": "success",
			"data":c.Param("id"),
		})



	}
}