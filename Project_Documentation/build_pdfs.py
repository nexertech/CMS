import os
import re
import subprocess
import markdown

DOCS_DIR = r"c:\xampp\htdocs\CMS\Project_Documentation"
CHROME_PATH = r"C:\Program Files\Google\Chrome\Application\chrome.exe"

HTML_TEMPLATE = """<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>{title}</title>
<style>
  @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap');

  @page {{
    size: A4;
    margin: 18mm 16mm 20mm 16mm;
    @bottom-right {{
      content: counter(page);
    }}
  }}

  body {{
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
    color: #1e293b;
    line-height: 1.6;
    font-size: 13.5px;
    background: #ffffff;
    margin: 0;
    padding: 0;
  }}

  /* Header Branding */
  .doc-banner {{
    border-bottom: 3px solid #0f172a;
    padding-bottom: 12px;
    margin-bottom: 24px;
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
  }}
  .doc-org {{
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    font-weight: 700;
    color: #0369a1;
  }}
  .doc-version {{
    font-size: 11px;
    font-weight: 600;
    color: #64748b;
  }}

  h1 {{
    font-size: 24px;
    font-weight: 800;
    color: #0f172a;
    margin-top: 0;
    margin-bottom: 8px;
    line-height: 1.25;
    letter-spacing: -0.5px;
  }}

  h2 {{
    font-size: 18px;
    font-weight: 700;
    color: #1e3a8a;
    border-bottom: 1.5px solid #e2e8f0;
    padding-bottom: 6px;
    margin-top: 26px;
    margin-bottom: 12px;
    page-break-after: avoid;
  }}

  h3 {{
    font-size: 15px;
    font-weight: 600;
    color: #0f172a;
    margin-top: 20px;
    margin-bottom: 8px;
    page-break-after: avoid;
  }}

  p, li {{
    color: #334155;
    margin-top: 4px;
    margin-bottom: 8px;
  }}

  ul, ol {{
    padding-left: 22px;
    margin-top: 4px;
    margin-bottom: 12px;
  }}

  li {{
    margin-bottom: 4px;
  }}

  /* Tables */
  table {{
    width: 100%;
    border-collapse: collapse;
    margin: 16px 0;
    font-size: 12px;
    page-break-inside: auto;
  }}

  tr {{
    page-break-inside: avoid;
    page-break-after: auto;
  }}

  th {{
    background-color: #0f172a;
    color: #ffffff;
    font-weight: 600;
    text-align: left;
    padding: 8px 10px;
    border: 1px solid #0f172a;
    letter-spacing: 0.3px;
  }}

  td {{
    padding: 7px 10px;
    border: 1px solid #e2e8f0;
    color: #334155;
    vertical-align: top;
  }}

  tbody tr:nth-child(even) {{
    background-color: #f8fafc;
  }}

  tbody tr:hover {{
    background-color: #f1f5f9;
  }}

  /* Code Blocks */
  pre {{
    background-color: #0f172a;
    color: #e2e8f0;
    padding: 12px 14px;
    border-radius: 6px;
    overflow-x: hidden;
    overflow-y: hidden;
    font-family: 'JetBrains Mono', Consolas, Monaco, monospace;
    font-size: 11px;
    line-height: 1.45;
    margin: 14px 0;
    page-break-inside: avoid;
    border: 1px solid #1e293b;
    box-sizing: border-box;
    width: 100%;
  }}

  code {{
    font-family: 'JetBrains Mono', Consolas, Monaco, monospace;
    font-size: 11px;
    background-color: #f1f5f9;
    color: #0369a1;
    padding: 2px 5px;
    border-radius: 4px;
    border: 1px solid #e2e8f0;
  }}

  pre code {{
    background-color: transparent;
    color: #e2e8f0;
    padding: 0;
    border: none;
    font-size: inherit;
  }}

  /* Blockquotes & Callouts */
  blockquote {{
    border-left: 4px solid #0284c7;
    background-color: #f0f9ff;
    color: #0369a1;
    margin: 14px 0;
    padding: 10px 14px;
    border-radius: 0 6px 6px 0;
    font-size: 12.5px;
  }}

  blockquote p {{
    margin: 0;
    color: #0c4a6e;
  }}

  /* Horizontal Rules */
  hr {{
    border: none;
    border-top: 1px solid #cbd5e1;
    margin: 22px 0;
  }}

  /* Footer */
  .doc-footer {{
    margin-top: 36px;
    border-top: 1px solid #e2e8f0;
    padding-top: 10px;
    display: flex;
    justify-content: space-between;
    font-size: 10.5px;
    color: #64748b;
  }}

  @media print {{
    body {{
      font-size: 12px;
    }}
    .doc-banner {{
      margin-bottom: 18px;
    }}
    h1 {{
      font-size: 21px;
    }}
    h2 {{
      font-size: 16px;
      margin-top: 20px;
    }}
    pre {{
      font-size: 9.6px !important;
      line-height: 1.35 !important;
      overflow: visible !important;
      overflow-x: hidden !important;
      padding: 10px 12px !important;
    }}
    ::-webkit-scrollbar {{
      display: none !important;
      width: 0 !important;
      height: 0 !important;
    }}
  }}
</style>
</head>
<body>
  <div class="doc-banner">
    <div>
      <div class="doc-org">Pakistan Navy Housing & CMES Directorate</div>
      <div style="font-size: 13px; font-weight: 700; color: #0f172a;">Complaint Management System (CMS)</div>
    </div>
    <div class="doc-version">Release v1.2.4</div>
  </div>

  {content}

  <div class="doc-footer">
    <div>&copy; 2026 Pakistan Navy CMS &bull; Nexertech Engineering</div>
    <div>Document Ref: {filename} &bull; Generated: September 2026</div>
  </div>
</body>
</html>
"""

def convert_md_to_pdf(md_filename):
    md_path = os.path.join(DOCS_DIR, md_filename)
    if not os.path.exists(md_path):
        print(f"Skipping {md_filename} - Not found")
        return

    base_name = os.path.splitext(md_filename)[0]
    html_path = os.path.join(DOCS_DIR, f"{base_name}.html")
    pdf_path = os.path.join(DOCS_DIR, f"{base_name}.pdf")

    with open(md_path, "r", encoding="utf-8") as f:
        md_text = f.read()

    # Extract title from first H1 or H2
    title_match = re.search(r"^#+\s*(.+)$", md_text, re.MULTILINE)
    title = title_match.group(1).strip() if title_match else base_name

    # Convert markdown to HTML with tables, fenced_code, and codehilite
    html_content = markdown.markdown(
        md_text,
        extensions=["tables", "fenced_code", "nl2br", "sane_lists"]
    )

    full_html = HTML_TEMPLATE.format(
        title=title,
        content=html_content,
        filename=md_filename
    )

    with open(html_path, "w", encoding="utf-8") as f:
        f.write(full_html)

    # Run Chrome headless print to PDF
    cmd = [
        CHROME_PATH,
        "--headless=new",
        "--disable-gpu",
        "--no-pdf-header-footer",
        f"--print-to-pdf={pdf_path}",
        html_path
    ]

    print(f"Generating PDF for {md_filename}...")
    res = subprocess.run(cmd, capture_output=True, text=True)
    if res.returncode == 0 and os.path.exists(pdf_path):
        size_kb = os.path.getsize(pdf_path) / 1024
        print(f" Successfully generated: {base_name}.pdf ({size_kb:.1f} KB)")
    else:
        print(f" Error generating PDF for {md_filename}: {res.stderr}")

    # Remove temporary HTML file
    if os.path.exists(html_path):
        os.remove(html_path)

if __name__ == "__main__":
    files = [
        "README.md",
        "01_URD_User_Requirements_Document.md",
        "02_SRS_Software_Requirements_Specification.md",
        "03_RBAC_Role_Based_Access_Control_Manual.md",
        "04_Developer_and_Technical_Manual.md",
        "05_Database_ERD_and_Data_Dictionary.md",
        "06_Code_Commenting_Standard_and_Audit.md",
        "07_System_User_and_Admin_Manual.md"
    ]

    for f in files:
        convert_md_to_pdf(f)

    print("\nAll PDFs processed successfully!")
