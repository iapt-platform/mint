package mint

import (
	"net/http"

	"github.com/gin-gonic/gin"
	"gorm.io/gorm"
)

type Login struct {
	User     string `form:"user" json:"user" xml:"user"  binding:"required"`
	Password string `form:"password" json:"password" xml:"password" binding:"required"`
}

func LoginDemo(_db *gorm.DB) gin.HandlerFunc {
	return func(c *gin.Context) {
		// TODO 在这里进行db操作
		var form Login
		if err := c.ShouldBindJSON(&form); err != nil {
			c.JSON(http.StatusBadRequest, gin.H{"error": err.Error()})
			return
		}

		if form.User != "manu" || form.Password != "123" {
			c.JSON(http.StatusUnauthorized, gin.H{"status": "unauthorized"})
			return
		}

		c.JSON(http.StatusOK, gin.H{
			"message": "pong",
		})
	}
}

func GetDemo(_db *gorm.DB) gin.HandlerFunc {
	return func(c *gin.Context) {
		// TODO 在这里进行db操作
		c.JSON(http.StatusOK, gin.H{
			"message": "pong",
		})
	}
}
