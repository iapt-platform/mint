package mint

import (
	"net/http"
	"time"

	"github.com/gin-gonic/gin"
	"github.com/go-pg/pg/v10"
)

type Bolds struct {
	Id        int    `json:"id" `
	BookId    int    `json:"book_id" `
	Paragraph int    `json:"paragraph" `
	WordSpell string `json:"word_spell" `
	WordReal  string `json:"word_real" `
	WordEn    string `json:"word_en" `
	CreatedAt time.Time
}

//display a list of all palitexts
func BoldsIndex(db *pg.DB) gin.HandlerFunc {
	return func(c *gin.Context) {
		word := c.Query("word")

		// TODO 补充业务逻辑
		var bolds []Bolds
		err := db.Model(&bolds).Where("word_real = ?", word).Select()
		if err != nil {
			panic(err)
		}

		c.JSON(http.StatusOK, gin.H{
			"status": "sucess",
			"data":   bolds,
		})
	}
}
