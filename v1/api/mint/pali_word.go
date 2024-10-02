package mint

import (
	"net/http"
	"time"

	"github.com/gin-gonic/gin"
	"github.com/go-pg/pg/v10"
)

type PaliWord struct {
	Id              int    `json:"id" `
	BookId          int    `json:"book_id" `
	Paragraph       int    `json:"paragraph" `
	PaliWordIndexId int    `json:"pali_word_index_id" `
	IsBase          bool   `json:"is_base" `
	Weight          string `json:"weight" `
	CreatedAt       time.Time
}

//display a list of all palitexts
func PaliWordsIndex(db *pg.DB) gin.HandlerFunc {
	return func(c *gin.Context) {
		book := c.Query("book")
		paragraph := c.Query("paragraph")

		// TODO 补充业务逻辑
		var pali_words []PaliWord
		err := db.Model(&pali_words).Where("book_id = ?", book).Where("paragraph = ?", paragraph).Select()
		if err != nil {
			panic(err)
		}

		c.JSON(http.StatusOK, gin.H{
			"status": "sucess",
			"data":   pali_words,
		})
	}
}
