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


type Term struct {
	Id     int `form:"id" json:"id" `
	PrParentId     int `form:"pr_parent_id" json:"pr_parent_id" `
	Word string `form:"word" json:"word" `
	WordEn string `form:"word_en" json:"word_en"`
	Tag string `form:"tag" json:"tag"`
	ChannelId string `form:"channel_id" json:"channel_id"`

	Meaning string `form:"meaning" json:"meaning"`
	Meaning2 string `form:"meaning2" json:"meaning2"`
	Note string `form:"note" json:"note"`
	Lang string `form:"lang" json:"lang"`
	Sourse string `form:"sourse" json:"sourse"`

	Confidence int `form:"confidence" json:"confidence"`

	OwnerId int
	Version int
    CreatedAt time.Time
    UpdatedAt time.Time
}


//display a list of all terms
func TermsIndex(db *pg.DB) gin.HandlerFunc {
	return func(c *gin.Context) {
		word:= c.DefaultQuery("word","")

		// TODO 补充业务逻辑
		var terms []Term
		err := db.Model(&terms).Column("id","word","type","grammar","meaning").Where("word = ? ",word).Select()
		if err != nil {
			panic(err)
		}
		
		c.JSON(http.StatusOK, gin.H{
			"message":"success",
			"data": terms,
		})
	}
}

//return an HTML form for creating a new term
func TermsNew(db *pg.DB) gin.HandlerFunc{
	return func(c *gin.Context){
		//TODO 业务逻辑
		c.HTML(http.StatusOK,"terms/new.html",gin.H{
			"message":"ok",
		})
	}
}
//create a new term
func TermsCreate(db *pg.DB) gin.HandlerFunc{
	return func(c *gin.Context){
	
		var form Term

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

//display a specific Term
func TermsShow(db *pg.DB, rdb *redis.Client) gin.HandlerFunc {
	return func(c *gin.Context) {
		id,err := strconv.Atoi(c.Param("id"))
		if err != nil {
			panic(err)
		}
		fmt.Println("get term id=" + c.Param("id"))
		rkey := "term://id/"+c.Param("id")
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

		term := &Term{Id: id}
		err = db.Model(term).Column("id","uid","name","description","description_type","owner_id","setting","status","version","updated_at").WherePK().Select()
		if err != nil {
			panic(err)
		}			

		//写入redis
		rdb.HSet(ctx,rkey,"id",term.Id)
		rdb.HSet(ctx,rkey,"pr_parent_id",term.PrParentId)
		rdb.HSet(ctx,rkey,"word",term.Word)
		rdb.HSet(ctx,rkey,"word_en",term.WordEn)
		rdb.HSet(ctx,rkey,"tag",term.Tag)
		rdb.HSet(ctx,rkey,"channel_id",term.ChannelId)
		rdb.HSet(ctx,rkey,"meaning",term.Meaning)
		rdb.HSet(ctx,rkey,"meaning2",term.Meaning2)
		rdb.HSet(ctx,rkey,"note",term.Note)
		rdb.HSet(ctx,rkey,"lang",term.Lang)
		rdb.HSet(ctx,rkey,"sourse",term.Sourse)
		rdb.HSet(ctx,rkey,"confidence",term.Confidence)
		rdb.HSet(ctx,rkey,"owner_id",term.OwnerId)
		rdb.HSet(ctx,rkey,"version",term.Version)
		rdb.HSet(ctx,rkey,"updated_at",term.UpdatedAt)

		c.JSON(http.StatusOK, gin.H{
			"message":"success",
			"data": term,
		})		
			
	}
}

//return an HTML form for edit a term
func TermsEdit(db *pg.DB) gin.HandlerFunc{
	return func(c *gin.Context){
		//TODO 业务逻辑
		c.HTML(http.StatusOK,"terms/edit.html",gin.H{
			"name":"ok",
		})
	}
}

//update a specific term
func TermsUpdate(db *pg.DB,rdb *redis.Client) gin.HandlerFunc{
	return func(c *gin.Context){
		var form Term

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
		rkey := "term://id/"+strconv.Itoa(form.Id)
		rdb.Del(ctx,rkey)	

		c.JSON(http.StatusOK,gin.H{
			"message":"success",
			"data":form,
		})

	}
}


//delete a specific term
func TermsDestroy(db *pg.DB ,rdb *redis.Client) gin.HandlerFunc{
	return func(c *gin.Context){
		id,err := strconv.Atoi(c.Param("id"))
		if err != nil {
			panic(err)
		}
		term := &Term{
			Id:int(id),
		}

		_, err = db.Model(term).WherePK().Delete()
		if err != nil {
			panic(err)
		}

		rkey := "term://id/"+c.Param("id")
		rdb.Del(ctx,rkey)
		
		c.JSON(http.StatusOK,gin.H{
			"message": "success",
			"data":c.Param("id"),
		})



	}
}