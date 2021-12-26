package mint

import (
	"net/http"
	"strconv"
	"fmt"
	"github.com/gin-gonic/gin"
	"github.com/go-pg/pg/v10"
	"github.com/go-redis/redis/v8"
	"time"
	"encoding/json"
)


type Sentence struct {
	Id     int `form:"id" json:"id" `
	IsPr bool `form:"is_pr" json:"is_pr" `
	BlockId string `form:"block_id" json:"block_id"`
	
	ChannelId int `form:"channel_id" json:"channel_id"`
	BookId int `form:"book_id" json:"book_id"`
	Paragraph int `form:"paragraph" json:"paragraph"`
	WordStart int `form:"word_start" json:"word_start"`
	WordEnd int `form:"word_end" json:"word_end"`

	Content string `form:"content" json:"content"`
	ContentType string `form:"content_type" json:"content_type"`
	
	Type string `form:"type" json:"type"`
	Lang string `form:"lang" json:"lang"`
	Status string `form:"status" json:"status"`

	EditorId int
	OwnerId int

	Version int
    DeletedAt time.Time

    CreatedAt time.Time
    UpdatedAt time.Time
}
type SentenceHolder struct{
	Data []Sentence
}
func (i *SentenceHolder) UnmarshalJSON(b []byte) error{
	return json.Unmarshal(b, &i.Data)
}

//display a list of all sentences
func SentencesIndex(db *pg.DB) gin.HandlerFunc {
	return func(c *gin.Context) {
		active:= c.Query("active")

		// TODO 补充业务逻辑
		var sentences []Sentence
		switch active {
			case "channel":
				//某channel句子列表
				channel := c.Query("channel")

				//request body sentence array with book para star end
			var form SentenceHolder

			if err := c.ShouldBindJSON(&form); err != nil {
				c.JSON(http.StatusBadRequest, gin.H{"error": err.Error()})
				return
			}
			sentences = make([]Sentence,0,len(form.Data))
			var oneSent Sentence
			count := 0 
			for _, value := range form.Data{
				err := db.Model(&oneSent).Column("id","book_id","paragraph","word_start","word_end","content","content_type").Where("channel = ?",channel).Where("book_id = ?",value.BookId).Where("paragraph = ?",value.Paragraph).Where("word_start = ?",value.WordStart).Where("word_end = ?",value.WordEnd).First()
				//errors.Is(err, gorm.ErrRecordNotFound)
				if err != nil {
					panic(err)
				}

				sentences[count] = oneSent
			}
			case "sentence": 
			//某句子所有channel记录
				book_id := c.Query("book")
				para := c.Query("para")
				start := c.Query("start")
				end := c.Query("end")
				err := db.Model(&sentences).Column("id","book_id","paragraph","word_start","word_end","content","content_type").Where("book_id = ?",book_id).Where("paragraph = ?",para).Where("word_start = ?",start).Where("word_end = ?",end).Select()
				if err != nil {
					panic(err)
				}
	
		}
		
		c.JSON(http.StatusOK, gin.H{
			"status":"succes",
			"message":"",
			"data": sentences,
		})
	}
}

//return an HTML form for creating a new sentence
func SentencesNew(db *pg.DB) gin.HandlerFunc{
	return func(c *gin.Context){
		//TODO 业务逻辑
		c.HTML(http.StatusOK,"sentences_new.html",gin.H{
			"message":"ok",
		})
	}
}


//create new sentence
func SentencesCreate(db *pg.DB) gin.HandlerFunc{
	return func(c *gin.Context){
		//channel := c.Query("channel")
		active := c.Query("active")

		var form SentenceHolder

		if err := c.ShouldBindJSON(&form); err != nil {
			c.JSON(http.StatusBadRequest, gin.H{"error": err.Error()})
			return
		}
		//TODO 查询权限
		switch active {
		case "create":
		case "pr":
		}
/*
		_, err := db.Model(newSentence).Insert()
		if err != nil {
			panic(err)
		}
*/
		//建立成功
		c.JSON(http.StatusOK,gin.H{
			"status":"succes",
			"message":"",
			"data":form.Data,
		})
	}
}

//display a specific Sentence
func SentencesShow(db *pg.DB, rdb *redis.Client) gin.HandlerFunc {
	return func(c *gin.Context) {
		id,err := strconv.Atoi(c.Param("id"))
		if err != nil {
			panic(err)
		}
		fmt.Println("get sentence id=" + c.Param("id"))
		rkey := "sentence://id"
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

		sentence := &Sentence{Id: id}
		err = db.Model(sentence).Column("id","uid","name","description","description_type","owner_id","setting","status","version","updated_at").WherePK().Select()
		if err != nil {
			panic(err)
		}			
		c.JSON(http.StatusOK, gin.H{
			"data": sentence,
		})
		//写入redis
		rdb.HSet(ctx,rkey,id,sentence)

	}
}

//return an HTML form for edit a sentence
func SentencesEdit(db *pg.DB) gin.HandlerFunc{
	return func(c *gin.Context){
		//TODO 业务逻辑
		c.HTML(http.StatusOK,"sentences_edit.html",gin.H{
			"name":"ok",
		})
	}
}
//update a specific sentence
func SentencesUpdate(db *pg.DB,rdb *redis.Client) gin.HandlerFunc{
	return func(c *gin.Context){
		var form Sentence

		if err := c.ShouldBindJSON(&form); err != nil {
			c.JSON(http.StatusBadRequest, gin.H{"error": err.Error()})
			return
		}

		//补充业务逻辑
		_,err := db.Model(&form).Column("name","description","description_type","status","setting").WherePK().Update()
		if err != nil {
			panic(err)
		}
		c.JSON(http.StatusOK,gin.H{
			"message":form,
		})
		//delete redis
		rkey := "sentence://id/"+strconv.Itoa(form.Id)
		rdb.Del(ctx,rkey)
	}
}


//delete a specific sentence
func SentencesDestroy(db *pg.DB ,rdb *redis.Client) gin.HandlerFunc{
	return func(c *gin.Context){
		id,err := strconv.Atoi(c.Param("id"))
		if err != nil {
			panic(err)
		}
		sentence := &Sentence{
			Id:int(id),
		}
		//删之前获取 course_id
		_, err = db.Model(sentence).WherePK().Delete()
		if err != nil {
			panic(err)
		}
		//删除article_list表相关项目
		c.JSON(http.StatusOK,gin.H{
			"message": c.Param("id"),
		})

		rkey := "sentence://id/"+c.Param("id")
		rdb.Del(ctx,rkey)

	}
}