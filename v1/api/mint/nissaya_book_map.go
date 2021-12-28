package mint

import (
	"net/http"
	"time"

	"github.com/gin-gonic/gin"
	"github.com/go-pg/pg/v10"
)

type NissayaBookMap struct {
	Id int `json:"id" `

	NsyBookId int    `json:"nsy_book_id" `
	BookId    int    `json:"book_id" `
	Vol       int    `json:"vol" `
	Title     string `json:"title" `
	Type      string `json:"type" `

	CreatedAt time.Time
}

//display a list of all palitexts
func NissayaBookMapsIndex(db *pg.DB) gin.HandlerFunc {
	return func(c *gin.Context) {
		book := c.Query("book")
		paragraph := c.Query("paragraph")

		// TODO 补充业务逻辑
		var nissaya_book_maps []NissayaBookMap
		err := db.Model(&nissaya_book_maps).Where("book_id = ?", book).Where("paragraph = ?", paragraph).Select()
		if err != nil {
			panic(err)
		}

		c.JSON(http.StatusOK, gin.H{
			"status": "sucess",
			"data":   nissaya_book_maps,
		})
	}
}
