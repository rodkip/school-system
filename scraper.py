import requests
from bs4 import BeautifulSoup
import json

# URL to scrape
url = "https://www.php.net/news"

# Send HTTP request to fetch the page content
response = requests.get(url)

# Parse the page content using BeautifulSoup
soup = BeautifulSoup(response.text, 'html.parser')

# List to store the project details (news titles and URLs)
news = []

# Find all the article links on the page
for article in soup.find_all('div', class_='news-entry'):
    title = article.find('a').get_text()
    link = "https://www.php.net" + article.find('a')['href']
    news.append({'title': title, 'url': link})

# Output JSON data
print(json.dumps(news))
