package mint

import (
	"net/http"
	"time"

	"github.com/gin-gonic/gin"
	"github.com/go-pg/pg/v10"
)

type MultiEditionPageNumber struct {
	Id        int    `json:"id" `
	Edition   string `json:"edition" `
	BookId    int    `json:"book_id" `
	Paragraph int    `json:"paragraph" `
	Vol       int    `json:"vol" `
	Page      string `json:"page" `
	CreatedAt time.Time
}

//display a list of all palitexts
func MultiEditionPageNumbersIndex(db *pg.DB) gin.HandlerFunc {
	return func(c *gin.Context) {
		book := c.Query("book")
		paragraph := c.Query("paragraph")

		// TODO 补充业务逻辑
		var multi_edition_page_numbers []MultiEditionPageNumber
		err := db.Model(&multi_edition_page_numbers).Where("book_id = ?", book).Where("paragraph = ?", paragraph).Select()
		if err != nil {
			panic(err)
		}

		c.JSON(http.StatusOK, gin.H{
			"status": "sucess",
			"data":   multi_edition_page_numbers,
		})
	}
}
