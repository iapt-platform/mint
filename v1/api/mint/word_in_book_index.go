package mint

import (
	"net/http"
	"time"

	"github.com/gin-gonic/gin"
	"github.com/go-pg/pg/v10"
)

type WordInBookIndex struct {
	Id              int `json:"id" `
	BookId          int `json:"book_id" `
	PaliWordIndexId int `json:"pali_word_index_id" `
	Count           int `json:"count" `
	CreatedAt       time.Time
}

//display a list of all palitexts
func WordInBookIndexsIndex(db *pg.DB) gin.HandlerFunc {
	return func(c *gin.Context) {
		book := c.Query("book")
		paragraph := c.Query("paragraph")

		// TODO 补充业务逻辑
		var word_in_book_indexs []WordInBookIndex
		err := db.Model(&word_in_book_indexs).Where("book_id = ?", book).Where("paragraph = ?", paragraph).Select()
		if err != nil {
			panic(err)
		}

		c.JSON(http.StatusOK, gin.H{
			"status": "sucess",
			"data":   word_in_book_indexs,
		})
	}
}
