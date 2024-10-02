package mint

import (
	"net/http"
	"time"

	"github.com/gin-gonic/gin"
	"github.com/go-pg/pg/v10"
)

type WbwTemplate struct {
	Id int `json:"id" `

	BookId    int    `json:"book_id" `
	Paragraph int    `json:"paragraph" `
	WordSn    int    `json:"word_sn" `
	Word      string `json:"word" `
	RealWord  string `json:"real_word" `
	Type      string `json:"type" `
	Grammar   string `json:"grammar" `
	Factors   string `json:"factors" `

	CreatedAt time.Time
}

//display a list of all palitexts
func WbwTemplatesIndex(db *pg.DB) gin.HandlerFunc {
	return func(c *gin.Context) {
		book := c.Query("book")
		paragraph := c.Query("paragraph")

		// TODO 补充业务逻辑
		var wbw_template []WbwTemplate
		err := db.Model(&wbw_template).Where("book_id = ?", book).Where("paragraph = ?", paragraph).Select()
		if err != nil {
			panic(err)
		}

		c.JSON(http.StatusOK, gin.H{
			"status": "sucess",
			"data":   wbw_template,
		})
	}
}
