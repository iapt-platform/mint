package mint

import (
	"net/http"
	"strconv"
	"time"

	"github.com/gin-gonic/gin"
	"github.com/go-pg/pg/v10"
	"github.com/go-redis/redis/v8"
)

type SentencesHistoray struct {
	Id          int    `form:"id" json:"id" `
	SentenceId  int    `form:"sentence_id" json:"sentence_id" `
	Content     string `form:"content" json:"content" `
	ContentType string `form:"content_type" json:"content_type"`

	EditorId int

	CreatedAt time.Time
}

//display a list of all sentencesHistorays
func SentencesHistoraiesIndex(db *pg.DB) gin.HandlerFunc {
	return func(c *gin.Context) {
		sentence_id := c.Query("sentence_id")

		// TODO 补充业务逻辑
		var sentencesHistorays []SentencesHistoray
		err := db.Model(&sentencesHistorays).Column("id", "sentence_id", "content", "content_type", "editor_id", "created_at").Where("sentence_id = ?", sentence_id).Select()
		if err != nil {
			panic(err)
		}

		c.JSON(http.StatusOK, gin.H{
			"status": "success",
			"data":   sentencesHistorays,
		})
	}
}

//create a new sentencesHistoray
func SentencesHistoraiesCreate(db *pg.DB) gin.HandlerFunc {
	return func(c *gin.Context) {

		var form SentencesHistoray

		if err := c.ShouldBindJSON(&form); err != nil {
			c.JSON(http.StatusBadRequest, gin.H{"error": err.Error()})
			return
		}
		form.EditorId = 1
		//TODO补充业务逻辑
		_, err := db.Model(&form).Insert()
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

//display a specific SentencesHistoray
func SentencesHistoraiesShow(db *pg.DB, rdb *redis.Client) gin.HandlerFunc {
	return func(c *gin.Context) {
		id, err := strconv.Atoi(c.Param("id"))
		if err != nil {
			panic(err)
		}

		sentencesHistoray := &SentencesHistoray{Id: id}
		err = db.Model(sentencesHistoray).Column("id", "sentence_id", "content", "content_type", "editor_id", "created_at").WherePK().First()
		if err != nil {
			panic(err)
		}

		c.JSON(http.StatusOK, gin.H{
			"status": "success",
			"data":   sentencesHistoray,
		})

	}
}
