package mint

import (
	"net/http"
	"time"

	"github.com/gin-gonic/gin"
	"github.com/go-pg/pg/v10"
)

type SubBook struct {
	Id        int    `json:"id" `
	BookId    int    `json:"book_id" `
	Paragraph int    `json:"paragraph" `
	Title     string `json:"title" `
	SetTitle  string `json:"set_title" `
	CreatedAt time.Time
}

//display a list of all palitexts
func SubBooksIndex(db *pg.DB) gin.HandlerFunc {
	return func(c *gin.Context) {
		book := c.Query("book")
		paragraph := c.Query("paragraph")

		// TODO 补充业务逻辑
		var subBook []SubBook
		err := db.Model(&subBook).Where("book_id = ?", book).Where("paragraph = ?", paragraph).Select()
		if err != nil {
			panic(err)
		}

		c.JSON(http.StatusOK, gin.H{
			"status": "sucess",
			"data":   subBook,
		})
	}
}
