package mint

import (
	"net/http"
	"time"

	"github.com/gin-gonic/gin"
	"github.com/go-pg/pg/v10"
)

type CsParanumber struct {
	Id          int    `json:"id" `
	BookId      int    `json:"book_id" `
	Paragraph   int    `json:"paragraph" `
	SubBookId   int    `json:"sub_book_id" `
	CsParagraph int    `json:"cs_paragraph" `
	BookName    string `json:"book_name" `
	CreatedAt   time.Time
}

//display a list of all palitexts
func CsParaNumbersIndex(db *pg.DB) gin.HandlerFunc {
	return func(c *gin.Context) {
		book := c.Query("book")
		paragraph := c.Query("paragraph")

		// TODO 补充业务逻辑
		var cs_para_numbers []CsParanumber
		err := db.Model(&cs_para_numbers).Where("book_id = ?", book).Where("paragraph = ?", paragraph).Select()
		if err != nil {
			panic(err)
		}

		c.JSON(http.StatusOK, gin.H{
			"status": "sucess",
			"data":   cs_para_numbers,
		})
	}
}
