package mint

import (
	"net/http"
	"time"

	"github.com/gin-gonic/gin"
	"github.com/go-pg/pg/v10"
)

type NissayaPageMap struct {
	Id             int    `json:"id" `
	Type           string `json:"type" `
	NsyBookId      int    `json:"nsy_book_id" `
	BookPageNumber int    `json:"book_page_number" `
	NsyId          int    `json:"nsy_id" `
	NsyPageNumber  string `json:"nsy_page_number" `
	NsyName        string `json:"nsy_name" `
	CreatedAt      time.Time
}

//display a list of all palitexts
func NissayaPageMapsIndex(db *pg.DB) gin.HandlerFunc {
	return func(c *gin.Context) {
		book := c.Query("book")
		paragraph := c.Query("paragraph")

		// TODO 补充业务逻辑
		var nissaya_page_maps []NissayaPageMap
		err := db.Model(&nissaya_page_maps).Where("book_id = ?", book).Where("paragraph = ?", paragraph).Select()
		if err != nil {
			panic(err)
		}

		c.JSON(http.StatusOK, gin.H{
			"status": "sucess",
			"data":   nissaya_page_maps,
		})
	}
}
